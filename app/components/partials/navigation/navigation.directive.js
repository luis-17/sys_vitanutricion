(function() {
  'use strict';

  angular
    .module('minotaur')
    .directive('minotaurNav', minotaurNav);

  /** @ngInject */
  function minotaurNav($timeout, $window) {
    var directive = {
      restrict: 'E',
      templateUrl: 'app/components/partials/navigation/navigation.html',
      controller: NavigationController,
      controllerAs: 'navigation',
      bindToController: true,
      link: function (scope, $el) {

        $timeout(function(){

          var $dropdowns = $el.find('ul').parent('li'),
            $a = $dropdowns.children('a'),
            $notDropdowns = $el.find('.nav-sidebar').children('li').not($dropdowns),
            $notDropdownsLinks = $notDropdowns.children('a'),
            app = angular.element('.appWrap'),
            $navigation_toggle = app.find('#navigation-toggle'),
            w = angular.element($window);

          $el.on('mouseenter', function() {
            if (app.hasClass('hz-menu') && !app.hasClass('viewport-sm')) {
              $el.addClass('nav-expanded');
              app.addClass('nav-expanded');
            }
          });
          $el.on('mouseleave', function() {
            if (app.hasClass('hz-menu') && !app.hasClass('viewport-sm')) {
              $el.removeClass('nav-expanded');
              app.removeClass('nav-expanded');
            }
          });

          $dropdowns.addClass('dropdown');

          var $submenus = $dropdowns.find('ul >.dropdown');
          $submenus.addClass('submenu');

          $a.append('<span class="indicator"></span>');

          $a.on('click', function(event) {

            var $this = angular.element(this),
              $parent = $this.parent('li'),
              $openSubmenu = angular.element('.submenu.open');

            if (!$parent.hasClass('submenu')) {
              $dropdowns.not($parent).removeClass('open').find('ul').slideUp();
            }

            $openSubmenu.not($this.parents('.submenu')).removeClass('open').find('ul').slideUp();
            $parent.toggleClass('open').find('>ul').stop().slideToggle();
            event.preventDefault();
          });

          $notDropdownsLinks.on('click', function() {
            $dropdowns.removeClass('open').find('ul').slideUp();
          });

          var $activeDropdown = angular.element('.dropdown>ul>.active').parent();

          $activeDropdown.css('display', 'block');

          scope.$on('$stateChangeSuccess', function(event, toState, toParams, fromState){
            if ($el.find('ul').parent('li.open.active').length > 0 && toState.parent !== fromState.parent) {
              $dropdowns.find('ul').slideUp();
            }
            if (app.hasClass('viewport-sm')) {
              $el.addClass('navigation-hidden');
              app.addClass('navigation-hidden');
            }
          });

          $navigation_toggle.on('click', function(){
            if (app.hasClass('viewport-sm')) {
              $el.toggleClass('navigation-hidden');
              app.toggleClass('navigation-hidden');
            } else {
              $el.toggleClass('navigation-sm');
              app.toggleClass('navigation-sm');
            }
          });

          function setSmallNavigation(){
            if($window.innerWidth < 1200 && $window.innerWidth > 768){
              $el.addClass('navigation-sm');
              app.addClass('navigation-sm');
              $el.removeClass('navigation-hidden');
              app.removeClass('navigation-hidden');
            } else if ($window.innerWidth <= 768) {
              $el.removeClass('navigation-sm');
              app.removeClass('navigation-sm');
              $el.addClass('navigation-hidden');
              app.addClass('navigation-hidden');
            } else {
              $el.removeClass('navigation-sm');
              app.removeClass('navigation-sm');
              $el.removeClass('navigation-hidden');
              app.removeClass('navigation-hidden');
            }
          }

          setSmallNavigation();

          w.bind('resize', setSmallNavigation);

        });

      }
    };

    return directive;

    /** @ngInject */
    function NavigationController() {
      //var navigation = this;
    }
  }

})();
