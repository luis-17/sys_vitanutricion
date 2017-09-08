(function() {
  'use strict';
  angular
    .module('minotaur')
    .controller('DiaController', DiaController)
    .service('DiaServices', DiaServices);

  /** @ngInject */
  function DiaController($scope,diasServices) { 
    var vm = this;    
  }
  function DiaServices($http, $q, handle) {
    return({
        sListarDiasCbo: sListarDiasCbo
    });
    function sListarDiasCbo(pDatos) {
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url :  angular.patchURLCI + "Dia/listar_dias_cbo",
            data : datos
      });
      return (request.then(handle.success,handle.error));
    }
  }
})();
