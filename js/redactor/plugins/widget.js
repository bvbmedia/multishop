(function($R)
{
    $R.add('plugin', 'widget', {
        translations: {
            en: {
                "widget": "Widget",
                "widget-html-code": "Widget HTML Code"
            }
        },
        modals: {
            'widget':
                '<form action=""> \
                    <div class="form-item"> \
                        <label>## widget-html-code ## <span class="req">*</span></label> \
                        <textarea name="widget" style="height: 200px;"></textarea> \
                    </div> \
                </form>'
        },
        init: function(app)
        {
            this.app = app;
            this.lang = app.lang;
            this.opts = app.opts;
            this.toolbar = app.toolbar;
            this.component = app.component;
            this.insertion = app.insertion;
            this.inspector = app.inspector;
            this.selection = app.selection;
        },
        // messages
        onmodal: {
            widget: {
                opened: function($modal, $form)
                {
                    $form.getField('widget').focus();

                    if (this.$currentItem)
                    {
                        var widgetData = this.$currentItem.getData();

                        $form.getField('widget').val(widgetData.html);
                    }
                },
                insert: function($modal, $form)
                {
                    var data = $form.getData();
                    this._insert(data);
                }
            }
        },
        oncontextbar: function(e, contextbar)
        {
            var data = this.inspector.parse(e.target)
            if (!data.isFigcaption() && data.isComponentType('widget'))
            {
                var node = data.getComponent();
                var buttons = {
                    "edit": {
                        title: this.lang.get('edit'),
                        api: 'plugin.widget.open',
                        args: node
                    },
                    "remove": {
                        title: this.lang.get('delete'),
                        api: 'plugin.widget.remove',
                        args: node
                    }
                };

                contextbar.set(e, node, buttons, 'bottom');
            }
        },

        // public
        start: function()
        {
            var obj = {
                title: this.lang.get('widget'),
                api: 'plugin.widget.open'
            };

            var $button = this.toolbar.addButton('widget', obj);
            $button.setIcon('<i class="re-icon-widget"></i>');
        },
        open: function()
		{
            this.$currentItem = this._getCurrent();

            var options = {
                title: this.lang.get('widget'),
                width: '600px',
                name: 'widget',
                handle: 'insert',
                commands: {
                    insert: { title: (this.$currentItem) ? this.lang.get('save') : this.lang.get('insert') },
                    cancel: { title: this.lang.get('cancel') }
                }
            };

            this.app.api('module.modal.build', options);
		},
        remove: function(node)
        {
            this.component.remove(node);
        },

        // private
		_getCurrent: function()
		{
    		var current = this.selection.getCurrent();
    		var data = this.inspector.parse(current);
    		if (data.isComponentType('widget'))
    		{
        		return this.component.build(data.getComponent());
    		}
		},
		_insert: function(data)
		{
    		this.app.api('module.modal.close');

    		if (data.widget.trim() === '')
    		{
        	    return;
    		}

            var $component = this.component.create('widget', data.widget);
    		this.insertion.insertHtml($component);

		}
    });
})(Redactor);
(function($R)
{
    $R.add('class', 'widget.component', {
        mixins: ['dom', 'component'],
        init: function(app, el)
        {
            this.app = app;

            // init
            return (el && el.cmnt !== undefined) ? el : this._init(el);
        },
        getData: function()
        {
            return {
                html: this._getHtml()
            };
        },

        // private
        _init: function(el)
        {
            if (typeof el !== 'undefined')
            {
                var $node = $R.dom(el);
                var $figure = $node.closest('figure');
                if ($figure.length !== 0)
                {
                    this.parse($figure);
                }
                else
                {
                    this.parse('<figure>');
                    this.html(el);
                }
            }
            else
            {
                this.parse('<figure>');
            }


            this._initWrapper();
        },
        _getHtml: function()
        {
            var $wrapper = $R.dom('<div>');
            $wrapper.html(this.html());
            $wrapper.find('.redactor-component-caret').remove();

            return $wrapper.html();
        },
        _initWrapper: function()
        {
            this.addClass('redactor-component');
            this.attr({
                'data-redactor-type': 'widget',
                'tabindex': '-1',
                'contenteditable': false
            });
        }
    });
})(Redactor);