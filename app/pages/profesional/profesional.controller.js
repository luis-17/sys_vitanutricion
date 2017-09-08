(function() {
  'use strict';

  angular
    .module('minotaur')
    .controller('ProfesionalController', ProfesionalController)
    .service('ProfesionalServices', ProfesionalServices);

  /** @ngInject */
  function ProfesionalController($scope,$uibModal,$timeout,$filter,filterFilter, uiGridConstants,$document, alertify,toastr,ProfesionalServices,
      EspecialidadServices,GrupoServices,UsuarioServices, pinesNotifications) {

    var vm = this;
    vm.selectedItem = {};
    vm.options = {};
    vm.fDemo = {};
    vm.fotoCrop = false;

    vm.remove = function(scope) {
      scope.remove();
    };

    vm.toggle = function(scope) {
      scope.toggle();
    };

    vm.expandAll = function() {
      vm.$broadcast('angular-ui-tree:expand-all');
    };

    // GRILLA PRINCIPAL
      var paginationOptions = {
        pageNumber: 1,
        firstRow: 0,
        pageSize: 10,
        sort: uiGridConstants.DESC,
        sortName: null,
        search: null
      };
      vm.mySelectionGrid = [];
      vm.gridOptions = {
        paginationPageSizes: [10, 50, 100, 500, 1000],
        paginationPageSize: 10,
        enableFiltering: true,
        enableSorting: true,
        useExternalPagination: true,
        useExternalSorting: true,
        useExternalFiltering : true,
        enableRowSelection: true,
        enableRowHeaderSelection: false,
        enableFullRowSelection: true,
        multiSelect: false,
        appScopeProvider: vm
      }
      vm.gridOptions.columnDefs = [
        { field: 'idprofesional', name:'idprofesional', displayName: 'ID', width: 80, enableFiltering: false, },
        { field: 'especialidad', name:'especialidad', displayName: 'ESPECIALIDAD', width: 200, },
        { field: 'nombre', name:'nombre', displayName: 'NOMBRE' },
        { field: 'apellidos', name:'apellidos', displayName: 'APELLIDOS', width: 150, },
        { field: 'correo', name:'correo', displayName: 'CORREO', width: 150, },
        { field: 'fecha_nacimiento', name:'fecha_nacimiento', displayName: 'FEC.NACIMIENTO', enableFiltering: false, },        
        { field: 'accion', name:'accion', displayName: 'ACCION', width: 80, enableFiltering: false,
          cellTemplate:'<label class="btn btn-sm text-primary" ng-click="grid.appScope.btnEditar(row);$event.stopPropagation();" tooltip-placement="left" uib-tooltip="EDITAR"> <i class="fa fa-edit"></i> </label>'+
          '<label class="btn btn-sm text-red" ng-click="grid.appScope.btnAnular(row);$event.stopPropagation();"> <i class="fa fa-trash" tooltip-placement="left" uib-tooltip="ELIMINAR!"></i> </label>'
         },

      ];
      vm.gridOptions.onRegisterApi = function(gridApi) {
        vm.gridApi = gridApi;
        gridApi.selection.on.rowSelectionChanged($scope,function(row){
          vm.mySelectionGrid = gridApi.selection.getSelectedRows();
        });
        gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
          vm.mySelectionGrid = gridApi.selection.getSelectedRows();
        });
        gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
          paginationOptions.pageNumber = newPage;
          paginationOptions.pageSize = pageSize;
          paginationOptions.firstRow = (paginationOptions.pageNumber - 1) * paginationOptions.pageSize;
          vm.getPaginationServerSide();
        });
        vm.gridApi.core.on.filterChanged( $scope, function(grid, searchColumns) {
          var grid = this.grid;
          paginationOptions.search = true;
          paginationOptions.searchColumn = {
            'e.especilidad' : grid.columns[2].filters[0].term,
            'p.nombre' : grid.columns[3].filters[0].term,
            'p.apellidos' : grid.columns[4].filters[0].term,
            'correo' : grid.columns[5].filters[0].term,        
          }
          // console.log('columnas',paginationOptions.searchColumn);
          vm.getPaginationServerSide();
        });
      }

      paginationOptions.sortName = vm.gridOptions.columnDefs[0].name;
      vm.getPaginationServerSide = function() {
        vm.datosGrid = {
          paginate : paginationOptions
        };
        ProfesionalServices.sListarProfesional(vm.datosGrid).then(function (rpta) {
          vm.gridOptions.data = rpta.datos;
          vm.gridOptions.totalItems = rpta.paginate.totalRows;
          vm.mySelectionGrid = [];
        });
      }
      vm.getPaginationServerSide();
      /*---------- NUEvA EMPRESA--------*/
      vm.btnNuevo = function () {
        var modalInstance = $uibModal.open({
          templateUrl: 'app/pages/profesional/profesional_formview.html',
          controllerAs: 'mp',
          size: 'lg',
          backdropClass: 'splash splash-2 splash-ef-14',
          windowClass: 'splash splash-2 splash-ef-14',          
          controller: function($scope, $uibModalInstance, arrToModal ){
            var vm = this;
            vm.fData = {};
            vm.modoEdicion = false;
            vm.getPaginationServerSide = arrToModal.getPaginationServerSide;
            vm.modalTitle = 'Registro de Profesional';

            EspecialidadServices.sListarEspecialidad().then(function (rpta) {
              vm.listaEspecialidades = angular.copy(rpta.datos);
              vm.fData.especialidad = vm.listaEspecialidades[0];
            });

            vm.getUsuarioAutocomplete = function (value) {
              var params = {};
              params.search= value;
              params.sensor= false;
                
              return UsuarioServices.sListaUsuarioAutocomplete(params).then(function(rpta) { 
                vm.noResultsLM = false;
                if( rpta.flag === 0 ){
                  vm.noResultsLM = true;
                }
                return rpta.datos; 
              });
            }

            vm.getSelectedUsuario = function($item, $model, $label){
              vm.fData.usuario = $item;
              vm.fData.idusuario = $model.idusuario;              
            }             

            /*------------  REGISTRO USUARIO  -----------------*/
            vm.user = function(){
              var modalInstance = $uibModal.open({
                templateUrl: 'user.html',
                controllerAs: 'us',
                size: 'md',
                backdropClass: 'splash',
                windowClass: 'splash',          
                controller: function($scope, $uibModalInstance, data ,arrToModal ){
                  var vm = this;
                  vm.fData = {};
                  vm.modoEdicion = false;
                  vm.getPaginationServerSide = arrToModal.getPaginationServerSide;
                  vm.modalTitle = 'Registro de Usuario'; 
                  GrupoServices.sListarGrupo().then(function (rpta) {
                    vm.listaGrupo = angular.copy(rpta.datos);
                    vm.fData.idgrupo = vm.listaGrupo[0];
                  });
                  // BOTONES
                  vm.aceptar = function () {
                    UsuarioServices.sRegistrarUsuario(vm.fData).then(function (rpta) {
                      if(rpta.flag == 1){ 
                        data.usuario = vm.fData.username;
                        data.idusuario = rpta.datos;
                        $uibModalInstance.close();          
                        var pTitle = 'OK!';
                        var pType = 'success';
                      }else if( rpta.flag == 0 ){
                        var pTitle = 'Advertencia!';
                        var pType = 'warning';  
                      }else{
                        alert('Ocurrió un error');
                      }
                      pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
                    });
                  };
                  vm.cancel = function () {
                    $uibModalInstance.dismiss('cancel');
                  };
                },
                resolve: {
                  data : function() { return vm.fData},
                  arrToModal: function() {
                    return {
                      getPaginationServerSide : vm.getPaginationServerSide 
                    }
                  }
                }
              });              
            }

            /*------------  FIN REGISTRO USUARIO  ------------*/   
            // SUBIDA DE IMAGENES MEDIANTE IMAGE CROP
            vm.fData.myImage='';
            vm.fData.myCroppedImage='';
            vm.cropType='circle';

            var handleFileSelect=function(evt) {
              var file = evt.currentTarget.files[0];
              var reader = new FileReader();
              reader.onload = function (evt) {
                /* eslint-disable */
                $scope.$apply(function(){
                  vm.fData.myImage=evt.target.result;
                });
                /* eslint-enable */
              };
              reader.readAsDataURL(file);
            };
            $timeout(function() { // lo pongo dentro de un timeout sino no funciona
              angular.element($document[0].querySelector('#fileInput')).on('change',handleFileSelect);
            }/* no delay here */);                     
            // BOTONES
            vm.aceptar = function () {
              // vm.fData.fecha_nacimiento = $filter('date')(new Date(vm.fData.fecha_nacimiento), 'yyyy-MM-dd ');
              vm.fData.idespecialidad = vm.fData.especialidad.id;
              ProfesionalServices.sRegistrarProfesional(vm.fData).then(function (rpta) {
                // var openedToasts = [];
                if(rpta.flag == 1){ 
                  $uibModalInstance.close(vm.fData);
                  vm.getPaginationServerSide();        
                  var pTitle = 'OK!';
                  var pType = 'success';
                }else if( rpta.flag == 0 ){
                  var pTitle = 'Advertencia!';
                  var pType = 'warning';  
                }else{
                  alert('Ocurrió un error');
                }
                pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
              });

            };
            vm.cancel = function () {
              $uibModalInstance.dismiss('cancel');
            };
          },
          resolve: {
            arrToModal: function() {
              return {
                getPaginationServerSide : vm.getPaginationServerSide,

              }
            }
          }
        });
      }
      /*-------- BOTONES DE EDICION ----*/
      vm.btnEditar = function(row){//datos personales
        var modalInstance = $uibModal.open({
          templateUrl: 'app/pages/profesional/profesional_formview.html',
          controllerAs: 'mp',
          size: 'lg',
          backdropClass: 'splash splash-2 splash-ef-14',
          windowClass: 'splash splash-2 splash-ef-14',
          // controller: 'ModalInstanceController',
          controller: function($scope, $uibModalInstance, arrToModal ){
            var vm = this;
            // var openedToasts = [];
            vm.fData = {};
            vm.fData = angular.copy(arrToModal.seleccion);
            vm.fData.afterUsuario = vm.fData.usuario;
            vm.modoEdicion = true;
            vm.getPaginationServerSide = arrToModal.getPaginationServerSide;

            vm.modalTitle = 'Edición de Profesional';
            EspecialidadServices.sListarEspecialidad().then(function (rpta) {
              vm.listaEspecialidades = angular.copy(rpta.datos);
              vm.fData.especialidad = vm.listaEspecialidades[0];
            });
            vm.getUsuarioAutocomplete = function (value) {
              var params = {};
              params.search= value;
              params.sensor= false;
                
              return UsuarioServices.sListaUsuarioAutocomplete(params).then(function(rpta) { 
                vm.noResultsLM = false;
                if( rpta.flag === 0 ){
                  vm.noResultsLM = true;
                }
                return rpta.datos; 
              });
            } 
            vm.getSelectedUsuario = function($item, $model, $label){
              vm.fData.usuario = $item;
              vm.fData.idusuario = $model.idusuario;
            }  
            /*------------  REGISTRO USUARIO  -----------------*/
            vm.user = function(){
              var modalInstance = $uibModal.open({
                templateUrl: 'user.html',
                controllerAs: 'us',
                size: 'md',
                backdropClass: 'splash',
                windowClass: 'splash',          
                controller: function($scope, $uibModalInstance, data ,arrToModal ){
                  var vm = this;
                  vm.fData = {};
                  vm.modoEdicion = false;
                  vm.getPaginationServerSide = arrToModal.getPaginationServerSide;
                  vm.modalTitle = 'Registro de Usuario';

                  GrupoServices.sListarGrupo().then(function (rpta) {
                    vm.listaGrupo = angular.copy(rpta.datos);
                    vm.fData.idgrupo = vm.listaGrupo[0];
                  });
                  // BOTONES
                  vm.aceptar = function () {
                    UsuarioServices.sRegistrarUsuario(vm.fData).then(function (rpta) { 
                      if(rpta.flag == 1){ 
                        data.usuario = vm.fData.username;
                        data.idusuario = rpta.datos;
                        $uibModalInstance.close();          
                        var pTitle = 'OK!';
                        var pType = 'success';
                      }else if( rpta.flag == 0 ){
                        var pTitle = 'Advertencia!';
                        var pType = 'warning';  
                      }else{
                        alert('Ocurrió un error');
                      }
                      pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
                    });

                  };
                  vm.cancel = function () {
                    $uibModalInstance.dismiss('cancel');
                  };
                },
                resolve: {
                  data : function() { return vm.fData},
                  arrToModal: function() {
                    return {
                      getPaginationServerSide : vm.getPaginationServerSide,
                    }
                  }
                }
              });              
            }
            /* --------- Editar datos del usuario -------*/
            vm.Edituser = function(){
              var modalInstance = $uibModal.open({
                templateUrl: 'user.html',
                controllerAs: 'us',
                size: 'md',
                backdropClass: 'splash',
                windowClass: 'splash',          
                controller: function($scope, $uibModalInstance, data ,modoEdit,arrToModal ){
                  var vm = this;
                  vm.fData = {};
                  //vm.fData.username = data.usuario;
                  //vm.fData.idusuario = data.idusuario;
                  vm.modoEdit = modoEdit;
                  vm.modalTitle = 'Edición de Usuario'; 

                  UsuarioServices.sMostrarUsuarioID(data).then(function(rptaUsu){
                    vm.fData.username = rptaUsu.datos[0]['username'];
                    vm.fData.idusuario = data.idusuario;                    
                    GrupoServices.sListarGrupo().then(function (rpta) {
                      vm.listaGrupo = angular.copy(rpta.datos);
                      angular.forEach(vm.listaGrupo, function(value, key){
                        if(value.id == rptaUsu.datos[0]['idgrupo']){
                          vm.fData.idgrupo = vm.listaGrupo[key];
                        }                      
                      });
                      
                    });                    
                  });          
                  // BOTONES
                  vm.aceptar = function () {
                    UsuarioServices.sEditarUsuario(vm.fData).then(function (rpta) { 
                      if(rpta.flag == 1){ 
                        data.usuario = vm.fData.username;
                        $uibModalInstance.close();          
                        var pTitle = 'OK!';
                        var pType = 'success';
                      }else if( rpta.flag == 0 ){
                        var pTitle = 'Advertencia!';
                        var pType = 'warning';  
                      }else{
                        alert('Ocurrió un error');
                      }
                      pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
                    });

                  };
                  vm.cancel = function () {
                    $uibModalInstance.dismiss('cancel');
                  };
                },
                resolve: {
                  data : function() { return vm.fData},
                  modoEdit : function() { return vm.modoEdicion},
                  arrToModal: function() {
                    return {
                      getPaginationServerSide : vm.getPaginationServerSide,
                    }
                  }
                }
              });              
            }

            /*------------  FIN REGISTRO USUARIO  ------------*/ 
            // SUBIDA DE IMAGENES MEDIANTE IMAGE CROP
            vm.cargarImagen = function(){
              vm.fotoCrop = true;
              vm.image = {
                 originalImage: '',
                 croppedImage: '',
              };             
              vm.cropType='circle';

              var handleFileSelect2=function(evt) {
                var file = evt.currentTarget.files[0];
                var reader = new FileReader();
                reader.onload = function (evt) {
                  /* eslint-disable */
                  $scope.$apply(function($scope){
                    vm.image.originalImage=evt.target.result;
                  });                 
                  /* eslint-enable */
                };
                reader.readAsDataURL(file);
              };
              $timeout(function() { // lo pongo dentro de un timeout sino no funciona
                angular.element($document[0].querySelector('#fileInput2')).on('change',handleFileSelect2);
              });
            }
            vm.subirFoto = function(){
              vm.image.nombre_foto = vm.fData.nombre_foto;
              vm.image.idprofesional = vm.fData.idprofesional;
              vm.image.nombre = vm.fData.nombre;
              ProfesionalServices.sSubirFoto(vm.image).then(function(rpta){

                if(rpta.flag == 1){ 
                  vm.fData.nombre_foto = rpta.datos;
                  arrToModal.seleccion.nombre_foto = rpta.datos;
                  vm.fotoCrop = false;
                  vm.image = {
                     originalImage: '',
                     croppedImage: '',
                  };       
                  var pTitle = 'OK!';
                  var pType = 'success';
                }else if( rpta.flag == 0 ){
                  var pTitle = 'Advertencia!';
                  var pType = 'warning';  
                }else{
                  alert('Ocurrió un error');
                }
                pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 }); 
              });
            }
            vm.cancelarFoto = function(){
              vm.fotoCrop = false;
              vm.image = {
                 originalImage: '',
                 croppedImage: '',
              };
            }                                   
            vm.aceptar = function () {
              // vm.fData.fecha_nacimiento = $filter('date')(new Date(vm.fData.fecha_nacimiento), 'yyyy-MM-dd ');
              vm.fData.idespecialidad = vm.fData.especialidad.id;
              ProfesionalServices.sEditarProfesional(vm.fData).then(function (rpta) {
                if(rpta.flag == 1){ 
                  vm.getPaginationServerSide();
                  $uibModalInstance.close();         
                  var pTitle = 'OK!';
                  var pType = 'success';
                }else if( rpta.flag == 0 ){
                  var pTitle = 'Advertencia!';
                  var pType = 'warning';  
                }else{
                  alert('Ocurrió un error');
                }
                pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
              });

            };
            vm.cancel = function () {
              if(vm.fData.afterUsuario != vm.fData.usuario && vm.modoEdicion ){
                vm.getPaginationServerSide();
              }
              $uibModalInstance.dismiss('cancel');
            };
          },
          resolve: {
            arrToModal: function() {
              return {
                getPaginationServerSide : vm.getPaginationServerSide,
                seleccion : row.entity
              }
            }
          }
        });
      }

      vm.btnAnular = function(row){
        alertify.confirm("¿Realmente desea realizar la acción?", function (ev) {
          ev.preventDefault();
          ProfesionalServices.sAnularProfesional(row.entity).then(function (rpta) { 
            if(rpta.flag == 1){ 
              vm.getPaginationServerSide();       
              var pTitle = 'OK!';
              var pType = 'success';
            }else if( rpta.flag == 0 ){
              var pTitle = 'Advertencia!';
              var pType = 'warning';  
            }else{
              alert('Ocurrió un error');
            }
            pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
          });
        }, function(ev) {
            ev.preventDefault();
        }); 
      }      

  }
  function ProfesionalServices($http, $q, handle) {
    return({
        sListarProfesional: sListarProfesional,
        sListarProfesionalCbo: sListarProfesionalCbo,
        sRegistrarProfesional: sRegistrarProfesional,
        sEditarProfesional: sEditarProfesional,
        sAnularProfesional: sAnularProfesional,
        sSubirFoto: sSubirFoto,
    });
    function sListarProfesional(pDatos) {
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url :  angular.patchURLCI + "Profesional/listar_profesional",
            data : datos
      });
      return (request.then( handle.success,handle.error ));
    }    
    function sListarProfesionalCbo(pDatos) {
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url :  angular.patchURLCI + "Profesional/listar_profesional_cbo",
            data : datos
      });
      return (request.then( handle.success,handle.error ));
    }
    function sRegistrarProfesional(pDatos) {
      var datos = pDatos || {};      
      var request = $http({
            method : "post",
            url : angular.patchURLCI + "Profesional/registrar_profesional",
            data : datos,
            headers: {'Content-Type': undefined},
            transformRequest: function (data) {
                var formData = new FormData();
                angular.forEach(data, function (value, key) {
                    formData.append(key, value);
                });
                return formData;
            }            
      });
      return (request.then(handle.success,handle.error));
    }      
    function sEditarProfesional(pDatos) {
      var datos = pDatos || {};      
      var request = $http({
            method : "post",
            url : angular.patchURLCI + "Profesional/editar_profesional",
            data : datos
      });
      return (request.then(handle.success,handle.error));
    }  
    function sAnularProfesional(pDatos) {
      var datos = pDatos || {};      
      var request = $http({
            method : "post",
            url : angular.patchURLCI + "Profesional/anular_profesional",
            data : datos
      });
      return (request.then(handle.success,handle.error));
    }
    function sSubirFoto(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Profesional/subir_foto_profesional",
            data : datos
      });
      return (request.then(handle.success,handle.error));
    }             
  }
})();
