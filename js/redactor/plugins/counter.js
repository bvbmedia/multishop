(function($R)
{
    $R.add('plugin', 'counter', {
        init: function(app)
        {
            this.app = app;
            this.editor = app.editor;
            this.statusbar = app.statusbar;
        },
        // public
        start: function()
        {
            var $editor = this.editor.getElement();
            $editor.on('keyup.redactor-plugin-counter', this.count.bind(this));

            this.count();
        },
        stop: function()
        {
            var $editor = this.editor.getElement();
            $editor.off('.redactor-plugin-counter');

            this.statusbar.removeItem('words');
            this.statusbar.removeItem('chars');
        },
		count: function()
		{
			var words = 0, characters = 0, spaces = 0;
			var $editor = this.editor.getElement();
			var html = $editor.html();

			html = this._clean(html)
			if (html !== '')
			{
				var arrWords = html.split(/\s+/);
				var arrSpaces = html.match(/\s/g);

				words = (arrWords) ? arrWords.length : 0;
				spaces = (arrSpaces) ? arrSpaces.length : 0;

				characters = html.length;
			}

            var data = { words: words, characters: characters, spaces: spaces };

            // callback
			this.app.broadcast('counter', data);

            // statusbar
            this.statusbar.add('words', 'words: ' + data.words);
            this.statusbar.add('chars', 'chars: ' + data.characters);
		},

        // private
        _clean: function(html)
        {
			html = html.replace(/<\/(.*?)>/gi, ' ');
			html = html.replace(/<(.*?)>/gi, '');
			html = html.replace(/\t/gi, '');
			html = html.replace(/\n/gi, ' ');
			html = html.replace(/\r/gi, ' ');
			html = html.replace(/\u200B/g, '');
			html = html.replace(/&nbsp;/g, '1');
			html = html.trim();

			return html;
        }
    });
})(Redactor);