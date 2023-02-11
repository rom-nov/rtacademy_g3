<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserAddType;
use App\Form\UserEditType;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
	private UserRepository $userRepository;
	public function __construct( UserRepository $userRepository )
	{
		$this->userRepository = $userRepository;
	}
    #[Route( '/user', name: 'users_list', methods: [ 'GET' ] )]
    public function index(): Response
    {
        return $this->render( 'user/index.html.twig' );
    }

	#[Route( '/user/list', name: 'users_list_all', methods: [ 'GET' ] )]
	public function list( Request $request ): Response
	{
		[
			$draw,
			$limit,
			$offset,
			$orderby,
			$direction
		] = $this->_getListData( $request->query );

		$criteria = [];

		$users = $this->userRepository->findBy(
			$criteria,
			[ $orderby => $direction ],
			$limit,
			$offset
		);

		$usersTotal = $this->userRepository->count( $criteria );

		return $this->json(
			[
				'data' => array_map( fn( $user ) =>
					[
						'id' => $user->getId(),
						'firstname' => $user->getFirstname(),
						'lastname' => $user->getLastname(),
						'login' => $user->getLogin(),
						'email' => $user->getEmail(),
					],
					$users
				),
				'recordsTotal'    => $usersTotal,
				'recordsFiltered' => $usersTotal,
				'draw'            => $draw,
			]
		);
	}

	protected function _getListData( InputBag $query ): array
	{
		$draw      = (int)$query->get( 'draw', 0 );
		$limit     = (int)$query->get( 'length', 10 );
		$offset    = (int)$query->get( 'start', 0 );
		$orderby   = $query->all( 'columns' )[ $query->all( 'order' )[0]['column'] ?? 0 ]['data'] ?? self::DEFAULT_LIST_ORDERBY_FIELD;
		$direction = $query->all( 'order' )[0]['dir'] ?? self::DEFAULT_LIST_ORDERBY_DIRECTION;

		return
			[
				$draw,
				$limit,
				$offset,
				$orderby,
				$direction,
			];
	}

	#[Route( '/user/view/{id<[0-9]+>}', name: 'users_view', methods: [ 'GET' ] )]
	public function view( int $id ): Response
	{
		$user = $this->userRepository->findOneBy( [ 'id' => $id ] );

		if( !$user )
		{
			throw $this->createNotFoundException( 'User #' . $id . ' not found' );
		}

		$roles = implode( ',', $user->getRoles() );

		return $this->render(
			'user/view.html.twig',
			[
				'user' => $user,
				'roles' => $roles
			]
		);
	}

	#[Route( '/user/add', name: 'users_add', methods: [ 'GET', 'POST' ] )]
	public function add( Request $request, ManagerRegistry $doctrine, UserPasswordHasherInterface $passwordHasher ): Response
	{
		$user = new User();
		$form = $this->createForm( UserAddType::class, $user );
		$form->handleRequest( $request );

		if( $form->isSubmitted() && $form->isValid() )
		{
			$user = $form->getData();
			//password
			$plaintextPassword = $user->getPassword();
			$hashedPassword = $passwordHasher->hashPassword(
				$user,
				$plaintextPassword
			);
			$user->setPassword($hashedPassword);
			//record in BD
			$entityManager = $doctrine->getManager();
			$entityManager->persist( $user );
			$entityManager->flush();

			$this->addFlash( 'success', 'User has been successfully added.' );

			return $this->redirectToRoute( 'users_list' );
		}

		return $this->render(
			'user/add.html.twig',
			[
				'form' => $form->createView(),
			]
		);
	}

	#[Route( '/user/delete/{id<[0-9]+>}', name: 'users_delete', methods: [ 'DELETE' ] )]
	public function delete( int $id, ManagerRegistry $doctrine ): Response
	{
		$user = $this->userRepository->findOneBy( [ 'id' => $id ] );

		if( !$user )
		{
			return $this->json(
				[
					'error' => 'User #' . $id . ' not found.'
				]
			);
		}

		$entityManager = $doctrine->getManager();
		$entityManager->remove( $user );
		$entityManager->flush();

		return $this->json(
			[
				'success' => 'User #' . $id . ' has been successfully deleted.'
			]
		);
	}

	#[Route( '/user/edit/{id<[0-9]+>}', name: 'users_edit', methods: [ 'GET', 'POST' ] )]
	public function edit( int $id, Request $request, ManagerRegistry $doctrine ): Response
	{
		$user = $this->userRepository->findOneBy( [ 'id' => $id ] );

		if( !$user )
		{
			throw $this->createNotFoundException( 'User #' . $id . ' not found' );
		}

		$form = $this->createForm( UserEditType::class, $user );
		$form->handleRequest( $request );

		if( $form->isSubmitted() && $form->isValid() )
		{
			$apiUser = $form->getData();
			$entityManager = $doctrine->getManager();
			$entityManager->persist( $apiUser );
			$entityManager->flush();

			$this->addFlash( 'success', 'User #' . $id . ' has been successfully updated.' );

			return $this->redirectToRoute( 'users_list' );
		}

		return $this->render(
			'user/edit.html.twig',
			[
				'form' => $form->createView(),
				'user' => $user,
			]
		);
	}
}
