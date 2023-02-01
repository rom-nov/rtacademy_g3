<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
				'data'            =>
					array_map(
						fn( $user ) =>
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

		return $this->render(
			'user/view.html.twig',
			[
				'user' => $user,
			]
		);
	}
}
