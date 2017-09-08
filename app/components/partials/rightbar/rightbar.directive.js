(function() {
  'use strict';

  angular
    .module('minotaur')
    .directive('minotaurRightbar', minotaurRightbar);

  /** @ngInject */
  function minotaurRightbar() {
    var directive = {
      restrict: 'E',
      templateUrl: 'app/components/partials/rightbar/rightbar.html',
      controller: RightbarController,
      controllerAs: 'rightbar',
      bindToController: true,
      link: function (scope, $el) {

          var app = angular.element('.appWrap'),
              $rightbar_toggle = app.find('#rightbar'),
              $close_righbar = $el.find('#close-rightbar');

          $rightbar_toggle.on('click', function(){
            $el.toggleClass('rightbar-expanded');
            app.toggleClass('rightbar-expanded');
          });

          $close_righbar.on('click', function(){
            $el.removeClass('rightbar-expanded');
            app.removeClass('rightbar-expanded');
          });
      }
    };

    return directive;

    /** @ngInject */
    function RightbarController() {
      //var rightbar = this;



    }
  }

})();
