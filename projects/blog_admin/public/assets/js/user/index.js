"use strict";

const renderActionsCell = ( data, type, row, meta ) =>
`
    <a href="/user/view/${row.id}" class="btn" title="View"><i class="uil-eye"></i></a>
    <a href="/user/edit/${row.id}" class="btn" title="Edit"><i class="uil-pen"></i></a>
    <a href="#" data-url="/user/delete/${row.id}" class="btn action-delete" title="Delete"><i class="uil-trash"></i></a>
`;

$( document ).ready( function()
{
    const dataTable = $( "#table" ).DataTable(
        {
            searching: false,
            lengthChange: false,
            processing: true,
            serverSide: true,
            ajax: '/user/list',
            columns: [
                { data: 'id' },
                { data: 'firstname', orderable: false },
                { data: 'lastname' },
                { data: 'login', orderable: false },
                { data: 'email', orderable: false },
                { render: renderActionsCell, orderable: false },
            ],
            keys : !0
        }
    );

    $('#table').on(
        'click',
        'a.action-delete',
        function( e )
        {
            e.preventDefault();
            $.ajax(
                {
                    'method'    : 'DELETE',
                    'url'       : $(this).data('url'),
                    'dataType'  : 'json',
                    'timeout'   : 60000,
                    'error'     : function( jqXHR, textStatus, errorThrown )
                    {
                        $('.wrapper .content-page .content').prepend(
                            `<div class="container-fluid mt-3">
                                <div class="alert alert-danger">
                                    Error: ${errorThrown}
                                </div>
                            </div>`);
                    },
                    'success'   : function( data, textStatus, jqXHR )
                    {
                        const
                            className = data.success ? 'success' : 'danger',
                            message = data.success ? data.success : data.error

                        $('.wrapper .content-page .content').prepend(
                            `<div class="container-fluid mt-3">
                                <div class="alert alert-${className}">
                                    ${message}
                                </div>
                            </div>`);

                        dataTable.draw();
                    }
                }
            );
        }
    );
} );