(function($, core, shell, undefined) {

shell.driver({
	name: 'theme',
	create: function() {
		var theme = shell.theme = shell.theme || {};
		if (shell.isTopicOnlyMode()) {
			$('body').addClass('topic-only');
		}
		else {
			// header
			var t = core.str(shell.setting('pageHeader.logoImage'), '');
			if (t) {
				$('#pageHeaderLogo').attr('src', t);
			}
			else {
				$('#pageHeaderLogo').hide();
			}
			t = shell.setting('pageHeader.visible');
			if (!t && t != undefined) {
				$('#pageHeader').hide();
				$('#pageMain').css('top', 0);
			}
			else {
				t = shell.setting('pageHeader.height');
				if (typeof t === 'number' && t > 0) {
					$('#pageHeader').height(t);
					$('#pageMain').css('top', t + 2);
				}
				if (shell.setting('pageHeader.showText', { types: 'boolean' }) === false) {
					$('#pageHeaderText').hide();
				}
			}
			// tabs
			var tabs = $('#pageSideTabs').tabs(),
				tabsHeader = $('#pageSideTabsHeader'),
				tabsContent = $('#pageSideTabsContent'),
				tabsNames = { toc: 0, index: 1, search: 2, 0: 0, 1: 1, 2: 2 },
				tabsHidden = {},
				rememberActiveTab = !!shell.setting('theme.rememberActiveTab'),
				cookieName = 'pageSideTabsActive',
				activeTab = rememberActiveTab && $.cookie && $.cookie(cookieName) ||
					tabsNames[((/(?:\?|&|^)tab=([^&]+)(?:&|$)/i.exec(location.search) || [])[1] || '0').toLowerCase()];
			$.each([ 'index', 'search' ], function(i, tab) {
				t = shell.setting(tab + '.visible');
				if (!t && t != undefined) {
					i = tabsNames[tab];
					tabsHidden[i] = true;
					tabsHeader.children().eq(i)
						.add(tabsContent.children().eq(i))
						.hide();
				}
			});
			var recalcTabsTop = theme.recalcTabsTop = function() {
				tabsContent.css('top', core.px2em(tabsContent, tabsHeader.outerHeight() + parseFloat(tabs.css('paddingTop')) + 3));
			}
			recalcTabsTop();
			tabs.bind('tabsshow', function(e, d) {
				if (rememberActiveTab && $.cookie) {
					$.cookie(cookieName, d.index || null, {
						expires: 365
					});
				}
			});
			if (activeTab && !tabsHidden[activeTab]) {
				tabs.tabs('select', +activeTab);
			}
			shell.switchTab = function(tab) {
				tab = tabsNames[tab];
				if (tab != undefined) {
					tabs.tabs('select', +tab);
				}
			};
			// splitter
			shell.bind('splitter', recalcTabsTop);
			// content
			if (shell.setting('topic.jqueryuiStyle') !== false) {
				$('#topicBlock').addClass('ui-widget-content');
			}
			// toolbar
			$('#topBar > .buttonset').buttonset();
			// topic-frame
			function calcFrameTop() {
				var fr = $('#topic .topic-frame');
				if (fr.length) {
					fr.css('top', $('#topicBar').outerHeight() + 2);
				}
			}
			$(window).resize(calcFrameTop);
			shell.bind('topicupdate breadcrumbsupdate', calcFrameTop);
		}
	}
});

})(jQuery, nethelp, nethelpshell);