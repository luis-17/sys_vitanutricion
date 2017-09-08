(function() {
  'use strict';
  angular
    .module('minotaur')
    .controller('EspecialidadController', EspecialidadController)
    .service('EspecialidadServices', EspecialidadServices);

  /** @ngInject */
  function EspecialidadController($scope,EspecilidadServices) {
    var vm = this;
  }
  function EspecialidadServices($http, $q, handle) {
    return({
        sListarEspecialidad: sListarEspecialidad
    });
    function sListarEspecialidad(pDatos) {
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url :  angular.patchURLCI + "especialidad/listar_especialidad",
            data : datos
      });
      return (request.then( handle.success,handle.error ));
    }
  }
})();