var Container = Class.create();
Container.prototype = {
	maxHeight: 4096,
	
	initialize: function() {
		gadgets.rpc.register('resize_iframe', this.setHeight);
		gadgets.rpc.register('set_pref', this.setUserPref);
		gadgets.rpc.register('set_title', this.setTitle);
		gadgets.rpc.register('requestNavigateTo', this.requestNavigateTo);
	},
	
	setHeight: function(height) {
		if ($(this.f) != undefined) {
			height += 28;
			if (height > gadgets.container.maxHeight) {
				height = gadgets.container.maxHeight;
			}
			Element.setStyle($(this.f), {'height':height+'px'});
		}
	},
	
	_parseIframeUrl: function(url) {
		var ret = new Object();
		var hashParams = url.replace(/#.*$/, '').split('&');
		var param = key = val = '';
		for (i = 0 ; i < hashParams.length; i++) {
			param = hashParams[i];
			key = param.substr(0, param.indexOf('='));
			val = param.substr(param.indexOf('=') + 1);
			ret[key] = val;
		}
		return ret;
	},
	
	setUserPref: function(editToken, name, value) {
		if ($(this.f) != undefined) {
			var params = gadgets.container._parseIframeUrl($(this.f).src);
			var url = web_prefix + '/prefs/set';
			new Ajax.Request(url, {method: 'get', parameters: { name: name, value: value, st: params.st }});
		}
	},
	
	setTitle: function(title) {
		var element = $(this.f+'_title');
		if (element != undefined) {
			element.update(title.replace(/&/g, '&amp;').replace(/</g, '&lt;'));
		}
	},
	
	_getUrlForView: function(view, person, app, mod) {
		if (view === 'home') {
			return web_prefix;
		} else if (view === 'profile') {
			return web_prefix + '/member/' + person;
		} else if (view === 'canvas') {
			return web_prefix + '/application/canvas/id/' + mod;
		} else {
			return null;
		}
	},
	
	requestNavigateTo: function(view, opt_params) {
		if ($(this.f) != undefined) {
			var params = gadgets.container._parseIframeUrl($(this.f).src);
			var url = gadgets.container._getUrlForView(view, params.owner, params.aid, params.mid);
			if (opt_params) {
				var paramStr = Object.toJSON(opt_params);
				if (paramStr.length > 0) {
					url += '?appParams=' + encodeURIComponent(paramStr);
				}
			}
			if (url && document.location.href.indexOf(url) == -1) {
	 			document.location.href = url;
			}
		}
	}
}

gadgets.container = new Container();
