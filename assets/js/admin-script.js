;(function($){

    var WCRWAdmin = {

        init: function() {
            $( 'select#wcrw_default_warranty\\[type\\], select#wcrw_product_warranty\\[type\\]' ).on( 'change', this.toggleTypeContent );
            $( 'select#wcrw_default_warranty\\[length\\], select#wcrw_product_warranty\\[length\\]' ).on( 'change', this.toggleLenghtContent );

            $( 'input#_wcrw_override_default_warranty' ).on( 'change', this.toggleDefaultWarranty );

            $( 'table.wcrw-addon-table').on( 'click', 'a.add-item', this.addRow );
            $( 'table.wcrw-addon-table').on( 'click', 'a.remove-item', this.removeRow );

            // Admin request notes
            $( 'form#request-note-form' ).on( 'submit', this.request.addNotes );
            $( '#wcrw-request-admin-notes' ).on( 'click', 'a.delete-note', this.request.deleteNotes );
            this.initialize();
        },

        initialize: function() {
            $( '.tips' ).tipTip({
                'attribute': 'data-tip',
                'defaultPosition': "top",
                'fadeIn': 50,
                'fadeOut': 50,
                'delay': 200
            });
            $( 'select#wcrw_default_warranty\\[type\\], select#wcrw_product_warranty\\[type\\], input#_wcrw_override_default_warranty' ).trigger( 'change' );
        },

        toggleDefaultWarranty: function(e) {
            e.preventDefault();
            var self = $(this);

            if ( self.is( ':checked' ) ) {
                self.closest( '#wcrw_warranty_tab' ).find('input,select').prop( 'disabled', false );
                self.closest( '#wcrw_warranty_tab' ).find('a.add-item,a.remove-item').show();
            } else {
                self.closest( '#wcrw_warranty_tab' ).find('input,select').not(self).prop( 'disabled', true );
                self.closest( '#wcrw_warranty_tab' ).find('a.add-item,a.remove-item').hide();
            }
        },

        toggleTypeContent: function(e) {
            e.preventDefault();

            var self = $(this),
                hide_classes = '.hide_if_no_warranty',
                show_classes = '.show_if_no_warranty',
                val  = self.val();

            $.each( [ 'included_warranty', 'addon_warranty' ], function( index, value ) {
                hide_classes = hide_classes + ', .hide_if_' + value;
                show_classes = show_classes + ', .show_if_' + value;
            });

            $(hide_classes).show();
            $(show_classes).hide();

            $('.show_if_' + val ).show();
            $('.hide_if_' + val ).hide();

            if ( val === 'included_warranty' ) {
                $( 'select#wcrw_default_warranty\\[length\\], select#wcrw_product_warranty\\[length\\]' ).trigger( 'change' );
            }
        },

        toggleLenghtContent: function(e) {
            e.preventDefault();

            var self = $(this),
                hide_classes = '.hide_if_lifetime, .hide_if_limited',
                show_classes = '.show_if_lifetime, .show_if_limited',
                val = self.val();

            $(hide_classes).show();
            $(show_classes).hide();

            $('.show_if_' + val ).show();
            $('.hide_if_' + val ).hide();
        },

        addRow: function(e){
            e.preventDefault();
            var row = $(this).closest('tr').first().clone().appendTo($(this).closest('tbody'));
            row.find('input').val('');
            row.find('select').val('days');
        },

        removeRow: function(e) {
            e.preventDefault();

            if( $(this).closest('tbody').find( 'tr' ).length == 1 ){
                return;
            }

            $(this).closest('tr').remove();
        },

        request: {
            addNotes: function(e) {
                e.preventDefault();
                var self = $(this),
                    data = {
                        action: 'add_request_note',
                        formData: self.serialize(),
                        nonce: wcrwadmin.nonce
                    };

                $('#wcrw-request-admin-notes').block({ message: null, overlayCSS: { background: '#fff url(' + wcrwadmin.ajax_loader + ') no-repeat center', opacity: 0.6 } });

                $.post( wcrwadmin.ajaxurl, data, function(resp) {
                    self.find( 'textarea' ).val('');
                    if ( resp.success ) {
                        $('#wcrw-request-admin-notes').find('.request-note-list').load( window.location.href + ' ul.request-note');
                    } else {
                        alert( resp.data );
                    }
                    $('#wcrw-request-admin-notes').unblock();
                } );
            },

            deleteNotes: function(e) {
                e.preventDefault();

                var self = $(this),
                    data = {
                        action: 'delete_request_note',
                        id: self.data('request_id'),
                        nonce: wcrwadmin.nonce
                    };

                $('#wcrw-request-admin-notes').block({ message: null, overlayCSS: { background: '#fff url(' + wcrwadmin.ajax_loader + ') no-repeat center', opacity: 0.6 } });

                $.post( wcrwadmin.ajaxurl, data, function(resp) {
                    if ( resp.success ) {
                        self.closest('li').remove();
                        $('#wcrw-request-admin-notes').unblock();
                    } else {
                        $('#wcrw-request-admin-notes').unblock();
                        alert( resp.data );
                    }
                } );
            }
        }
    };

    $(document).ready(function(){
        WCRWAdmin.init();
    });

})(jQuery);
