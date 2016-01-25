var FieldPaletteBackend =
{
    registerPlugins : function(){
        $$('ul.tl_fieldpalette_sortable').each(function(ul) {
            FieldPaletteBackend.makeFieldPaletteSortable(ul.id);
        });
    },
    deleteFieldPaletteEntry : function(el, id){
        new Request.Contao({
            'url':el.href,
            'followRedirects':false,
            onSuccess: function(txt, json) {
                FieldPaletteBackend.refreshFieldPalette(el.getParent('.fielpalette-wizard').getProperty('id'));
            }
        }).get();

        return false;
    },
    makeFieldPaletteSortable: function(ul) {
        var ds = new Scroller(document.getElement('body'), {
            onChange: function(x, y) {
                this.element.scrollTo(this.element.getScroll().x, y);
            }
        });

        var list = new Sortables(ul, {
            constrain: true,
            opacity: 0.6,
            onStart: function() {
                ds.start();
            },
            onComplete: function() {
                ds.stop();
            },
            handle: '.drag-handle'
        });

        list.active = false;

        list.addEvent('start', function() {
            list.active = true;
        });

        list.addEvent('complete', function(el) {
            if (!list.active) return;
            var id, pid, req, href;

            var handle = el.getElement('.tl_content_right ' + list.options.handle),
                href = handle.get('data-href'),
                id = handle.get('data-id'),
                pid = handle.get('data-pid');

            if (el.getPrevious('li')) {
                pid = el.getPrevious('li').getChildren('.tl_content_right ' + list.options.handle).get('data-id');
                href = href.replace(/id=[0-9]*/, 'id=' + id) + '&act=cut&mode=1&pid=' + pid;
                new Request.Contao({'url':href, 'followRedirects':false}).get();
            } else if (el.getParent('ul')) {
                href = href.replace(/id=[0-9]*/, 'id=' + id) + '&act=cut&mode=2&pid=' + pid;
                new Request.Contao({'url':href, 'followRedirects':false}).get();
            }
        });
    },
    /**
     * Open an iframe in a modal window
     *
     * @param {object} options An optional options object
     */
    openModalIframe: function (options) {
        var opt = options || {};
        var max = (window.getSize().y - 180).toInt();
        if (!opt.height || opt.height > max) opt.height = max;
        var M = new SimpleModal({
            'width': opt.width,
            'hideFooter': true,
            'draggable': false,
            'overlayOpacity': .5,
            'onShow': function () {
                document.body.setStyle('overflow', 'hidden');
            },
            'onHide': function () {
                FieldPaletteBackend.refreshFieldPalette(options.syncId);
                document.body.setStyle('overflow', 'auto');
            }
        });
        M.show({
            'title': opt.title,
            'contents': '<iframe src="' + opt.url + '" width="100%" height="' + opt.height + '" frameborder="0"></iframe>'
        });
    },
    refreshFieldPalette: function(id){

        var field = id.replace('ctrl_', '');

        new Request.Contao({
            onRequest: function(){
                $(id).getElement('.tl_fielpalette_indicator').show();
            },
            onSuccess: function(txt, json) {
                var tmp = new Element('div', {html: json.content});
                tmp.getFirst().replaces($(id));
                $(id).getElement('.tl_fielpalette_indicator').hide();
                FieldPaletteBackend.registerPlugins();
            }
        }).post({'action':'refreshFieldPaletteField', 'field' : field, 'REQUEST_TOKEN': Contao.request_token});

    }
}


// Initialize the back end script
window.addEvent('domready', function() {
    FieldPaletteBackend.registerPlugins();
});
