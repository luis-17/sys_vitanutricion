(function() {
  'use strict';
  angular
    .module('minotaur')
    .controller('UsuarioController', UsuarioController)
    .service('UsuarioServices', UsuarioServices);

  /** @ngInject */
  function UsuarioController($scope,UsuarioServices) {
    var vm = this;
  }
  function UsuarioServices($http, $q, handle) {
    return({
        sListarUsuario: sListarUsuario,    
        sRegistrarUsuario: sRegistrarUsuario,
        sEditarUsuario: sEditarUsuario,
        sAnularUsuario: sAnularUsuario,
        sListaUsuarioAutocomplete: sListaUsuarioAutocomplete, 
        sMostrarUsuarioID: sMostrarUsuarioID,
        sCambiarClave: sCambiarClave           
    });
    function sListarUsuario(pDatos) {
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url :  angular.patchURLCI + "Usuario/listar_usuario",
            data : datos
      });
      return (request.then( handle.success,handle.error ));
    }
    function sRegistrarUsuario(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Usuario/registrar_usuario",
            data : datos
      });
      return (request.then(handle.success,handle.error));
    }
    function sEditarUsuario(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Usuario/editar_usuario",
            data : datos
      });
      return (request.then(handle.success,handle.error));
    }
    function sAnularUsuario(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Usuario/anular_usuario",
            data : datos
      });
      return (request.then(handle.success,handle.error));
    }
    function sListaUsuarioAutocomplete(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Usuario/lista_usuario_autocomplete",
            data : datos
      });
      return (request.then(handle.success,handle.error));
    }
    function sMostrarUsuarioID(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Usuario/mostrar_usuario_id",
            data : datos
      });
      return (request.then(handle.success,handle.error));
    }    
    function sCambiarClave(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Usuario/cambiar_clave",
            data : datos
      });
      return (request.then(handle.success,handle.error));
    }     
  }
})();