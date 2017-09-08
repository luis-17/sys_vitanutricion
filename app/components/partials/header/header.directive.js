(function() {
  'use strict';

  angular
    .module('minotaur')
    .directive('minotaurHeader', minotaurHeader);

  /** @ngInject */
  function minotaurHeader($window) {
    var directive = {
      restrict: 'E',
      templateUrl: 'app/components/partials/header/header.html',
      controller: HeaderController,
      controllerAs: 'header',
      bindToController: true,
      link: function (scope, element) {
        var app = angular.element('.appWrap'),
            $el = angular.element(element),
            w = angular.element($window);

        function setViewport(){
          if($window.innerWidth < 768){
            $el.addClass('viewport-sm');
            app.addClass('viewport-sm');
          } else {
            $el.removeClass('viewport-sm');
            app.removeClass('viewport-sm');
          }
        }

        setViewport();

        w.bind('resize', setViewport);
      }
    };

    return directive;

    /** @ngInject */
    function HeaderController() {
      var vm = this;

      vm.notifications = [{
        content: 'User Imrich cancelled account',
        time: '6 minutes ago',
        icon: 'fa fa-ban',
        color: 'bg-danger'
      },{
        content: 'New user registered',
        time: '12 minutes ago',
        icon: 'fa fa-bolt',
        color: 'bg-primary'
      },{
        content: 'User Robert locked account',
        time: '18 minutes ago',
        icon: 'fa fa-lock',
        color: 'bg-greensea'
      },{
        content: 'User Jon cancelled account',
        time: '36 minutes ago',
        icon: 'fa fa-ban',
        color: 'bg-danger'
      },{
        content: 'User Joffrey has been muted',
        time: '42 minutes ago',
        icon: 'fa fa-bullseye',
        color: 'bg-warning'
      },{
        content: 'User Sam locked account',
        time: '58 minutes ago',
        icon: 'fa fa-lock',
        color: 'bg-greensea'
      }]

    }
  }

})();
