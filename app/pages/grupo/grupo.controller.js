(function() {
  'use strict';
  angular
    .module('minotaur')
    .controller('GrupoController', GrupoController)
    .service('GrupoServices', GrupoServices);

  /** @ngInject */
  function GrupoController($scope,GrupoServices) {
    var vm = this;
  }
  function GrupoServices($http, $q, handle) {
    return({
        sListarGrupo: sListarGrupo,        
    });
    function sListarGrupo(pDatos) {
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url :  angular.patchURLCI + "Grupo/listar_grupo",
            data : datos
      });
      return (request.then( handle.success,handle.error ));
    }
  }
})();