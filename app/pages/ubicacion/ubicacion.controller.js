(function() {
  'use strict';
  angular
    .module('minotaur')
    .controller('UbicacionController', UbicacionController)
    .service('UbicacionServices', UbicacionServices);

  /** @ngInject */
  function UbicacionController($scope,UbicacionServices) { 
    var vm = this;    
  }
  function UbicacionServices($http, $q, handle) { 
    return({
        sListarUbicacionCbo: sListarUbicacionCbo
    });
    function sListarUbicacionCbo(pDatos) {
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url :  angular.patchURLCI + "Ubicacion/listar_ubicacion_cbo",
            data : datos
      });
      return (request.then( handle.success,handle.error )); 
    }
  }
})();
