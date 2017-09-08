(function() {
  'use strict';

  angular
    .module('minotaur')
    .controller('LoginController', LoginController)
    .service('loginServices', loginServices);

    function handleError( response ) {
      if ( ! angular.isObject( response.data ) || ! response.data.message ) {
          return( $q.reject( "An unknown error occurred." ) );
      }
      return( $q.reject( response.data.message ) );
  }
  function handleSuccess( response ) {
      return( response.data );
  }

  /** @ngInject */
  function LoginController($scope,loginServices) {
  	$scope.getValidateSession();
  	$scope.btnLoginToSystem = function () { 
      if($scope.fLogin.usuario == null || $scope.fLogin.clave == null || $scope.fLogin.usuario == '' || $scope.fLogin.clave == ''){
        $scope.fAlert = {};
        $scope.fAlert.type= 'orange';
        $scope.fAlert.msg= 'Debe completar los campos usuario y contraseña.';
        $scope.fAlert.strStrong = 'Aviso.';
        return;
      }

      loginServices.sLoginToSystem($scope.fLogin).then(function (response) { 
        $scope.fAlert = {};
        if( response.flag == 1 ){ // SE LOGEO CORRECTAMENTE 
          $scope.fAlert.type= 'success';
          $scope.fAlert.msg= response.message;
          $scope.fAlert.strStrong = 'OK.';
          $scope.getValidateSession();
          $scope.logIn();
          // $scope.getNotificaciones();
        }else if( response.flag == 0 ){ // NO PUDO INICIAR SESION 
          $scope.fAlert.type= 'danger';
          $scope.fAlert.msg= response.message;
          $scope.fAlert.strStrong = 'Error.';
        }else if( response.flag == 2 ){  // CUENTA INACTIVA
          $scope.fAlert.type= 'orange';
          $scope.fAlert.msg= response.message;
          $scope.fAlert.strStrong = 'Aviso.';
          $scope.listaSedes = response.datos;
        }
        $scope.fAlert.flag = response.flag;
        //$scope.fLogin = {};
      });
    }
  }

  function loginServices($http, $q, handle) {
    return({
        sLoginToSystem: sLoginToSystem
    });
    function sLoginToSystem(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url :  angular.patchURLCI + "acceso/", 
            data : datos          
      });
      return (request.then( handle.success,handle.error ));
    }
  }

})();
