(function() {
  'use strict';
  angular
    .module('minotaur')
    .controller('AntecedenteController', AntecedenteController)
    .service('AntecedenteServices', AntecedenteServices);

  /** @ngInject */
  function AntecedenteController($scope,AntecedenteServices) {
    var vm = this;
  }
  function AntecedenteServices($http, $q) {
    return({

    });

  }
})();
