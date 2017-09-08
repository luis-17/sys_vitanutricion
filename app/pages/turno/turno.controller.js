(function() {
  'use strict';
  angular
    .module('minotaur')
    .controller('TurnoController', TurnoController)
    .service('TurnoServices', TurnoServices);

  /** @ngInject */
  function TurnoController($scope,TurnoServices) { 
    var vm = this;    
  }
  function TurnoServices($http, $q, handle) {
    return({
        sListarTurnosCbo: sListarTurnosCbo
    });
    function sListarTurnosCbo(pDatos) {
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url :  angular.patchURLCI + "Turno/listar_turnos_cbo",
            data : datos
      });
      return (request.then(handle.success,handle.error));
    }
  }
})();
