(function() {
  'use strict';
  angular
    .module('minotaur')
    .controller('ConsultasController', ConsultasController)
    .service('ConsultasServices', ConsultasServices);

  /** @ngInject */
  function ConsultasController ($scope,$uibModal,$window,alertify,ConsultasServices,pageLoading,PlanAlimentarioServices, pinesNotifications, ModalReporteFactory) { 
    var vm = this;

    vm.initConsulta = function(cita,origen,callback,tipoVista,tipoDieta/**/){
      pageLoading.start('Cargando formulario...');
      //var tipoDieta = tipoDieta || null;
      vm.cita = cita;
      // console.log(vm.cita.atencion.idatencion,$scope.idatencion,'vm.cita.atencion.idatencion,$scope.idatencion');
      if( !(vm.cita.atencion.idatencion) ){ 
        vm.cita.atencion.idatencion = $scope.idatencion; 
      }
      vm.fData = {};
      vm.origen = origen;
      vm.callback = callback;
      vm.tipoVista = tipoVista;
      
      //console.log('callback',callback);
      vm.emails = cita.cliente.email;

      if($scope.pestaniaConsulta){
        vm.changePestania($scope.pestaniaConsulta);
      }else{
        vm.changePestania(1);
      }
      // console.log($scope.tipoDieta,'$scope.tipoDieta',$scope.pestaniaConsulta,'$scope.pestaniaConsulta');
      if( $scope.tipoDieta ){
        vm.tipoVista = 'edit';
      }
      if(vm.tipoVista == 'new'){
        vm.fData = {};
        vm.fData.si_embarazo = false;
        vm.fData.fecha_atencion = moment(vm.cita.fecha).toDate(); 
        //console.log('vm.fData.fecha_atencion',vm.fData.fecha_atencion);
        pageLoading.stop();
      }else if(vm.tipoVista == 'edit'){ 
        // console.log('entro editar');
        ConsultasServices.sCargarConsulta(vm.cita).then(function(rpta){
          if(rpta.flag == 1){            
            vm.fData = rpta.datos;
            
            // console.log(vm.fData,'vm.fDataaaaaaaaaaaaaaaaaaaaaaa');
            $scope.tipoDieta = vm.fData.tipo_dieta; 
            vm.fData.fecha_atencion = moment(vm.fData.fecha_atencion).toDate();
            /*vm.fData.kg_masa_grasa = parseFloat(((parseFloat(vm.fData.peso) * parseFloat(vm.fData.porc_masa_grasa)) / 100).toFixed(2));
            vm.fData.kg_masa_libre = parseFloat(((parseFloat(vm.fData.peso) * parseFloat(vm.fData.porc_masa_libre)) / 100).toFixed(2));
            */
          }
          pageLoading.stop();
        });
      }
    }

    /*DATEPICKER*/
    vm.dp = {};
    vm.dp.today = function() {
      vm.fData.fecha_atencion = new Date();
    };

    vm.dp.clear = function() {
      vm.fData.fecha_atencion = null;
    };

    vm.dp.dateOptions = {
      formatYear: 'yy',
      maxDate: new Date(2020, 5, 22),
      startingDay: 1,
      placement:'bottom'
    };

    vm.dp.open = function() {
      vm.dp.popup.opened = true;
    };

    vm.dp.formats = ['dd/MM/yyyy', 'dd-MMMM-yyyy', 'yyyy/MM/dd', 'dd.MM.yyyy', 'shortDate'];
    vm.dp.format = vm.dp.formats[0];
    vm.dp.altInputFormats = ['M!/d!/yyyy'];

    vm.dp.popup = {
      opened: false
    };

    vm.isOpen = false;
    vm.titleToggle = "Ver edición avanzada.";
    vm.changeToggle = function(){
      if(vm.isOpen){
        vm.isOpen = false;
        vm.titleToggle = "Ver edición avanzada.";
      }else{
        vm.isOpen = true;
        vm.titleToggle = "Ocultar edición avanzada";
      }
    }

    vm.changePestania = function(value){
      if(value == 3 && $scope.origenConsulta && $scope.origenConsulta == 'plan'){
        vm.actualizarConsulta($scope.idatencion);
      }
      vm.pestania = value;
    }

    vm.changeEmbarazo = function(){
      if(vm.fData.si_embarazo){
        vm.fData.si_embarazo  = false;
      }else{
        vm.fData.si_embarazo  = true;
      }
    }

    vm.changeComposicion = function(value){
      if(value == 'peso'){
        if(vm.fData.porc_masa_grasa && vm.fData.porc_masa_grasa != null && vm.fData.porc_masa_grasa != ''){
          vm.fData.porc_masa_grasa = parseFloat((100 - parseFloat(vm.fData.porc_masa_libre)).toFixed(2));
          vm.fData.kg_masa_grasa = parseFloat(((parseFloat(vm.fData.peso) * parseFloat(vm.fData.porc_masa_grasa)) / 100).toFixed(2));
        }

        if(vm.fData.porc_masa_libre && vm.fData.porc_masa_libre != null && vm.fData.porc_masa_libre != ''){
          vm.fData.porc_masa_libre = parseFloat((100 - parseFloat(vm.fData.porc_masa_grasa)).toFixed(2));
          vm.fData.kg_masa_libre = parseFloat(((parseFloat(vm.fData.peso) * parseFloat(vm.fData.porc_masa_libre)) / 100).toFixed(2));
        }
      }else if(vm.fData.peso && vm.fData.peso != null && vm.fData.peso != ''){
        //console.log(value);
        if(value == 'porc_masa_grasa'){
          vm.fData.porc_masa_libre = parseFloat((100 - parseFloat(vm.fData.porc_masa_grasa)).toFixed(2));
          vm.fData.kg_masa_libre = parseFloat(((parseFloat(vm.fData.peso) * parseFloat(vm.fData.porc_masa_libre)) / 100).toFixed(2));
          vm.fData.kg_masa_grasa = parseFloat((parseFloat(vm.fData.peso) - parseFloat(vm.fData.kg_masa_libre)).toFixed(2));
        }

        if(value == 'porc_masa_libre'){
          vm.fData.porc_masa_grasa = parseFloat((100 - parseFloat(vm.fData.porc_masa_libre)).toFixed(2));
          vm.fData.kg_masa_grasa = parseFloat(((parseFloat(vm.fData.peso) * parseFloat(vm.fData.porc_masa_grasa)) / 100).toFixed(2));
          vm.fData.kg_masa_libre = parseFloat((parseFloat(vm.fData.peso) - parseFloat(vm.fData.kg_masa_grasa)).toFixed(2));
        }

        if(value == 'porc_masa_muscular'){
          vm.fData.kg_masa_muscular = parseFloat(((parseFloat(vm.fData.peso) * parseFloat(vm.fData.porc_masa_muscular)) / 100).toFixed(2));
        }

        if(value == 'kg_masa_muscular'){
          vm.fData.porc_masa_muscular = parseFloat(((parseFloat(vm.fData.kg_masa_muscular) * 100) / parseFloat(vm.fData.peso)).toFixed(2));
        }

        if(value == 'porc_agua_corporal'){
          vm.fData.kg_agua_corporal = parseFloat(((parseFloat(vm.fData.peso) * parseFloat(vm.fData.porc_agua_corporal)) / 100).toFixed(2));
        }

        if(value == 'kg_agua_corporal'){
          vm.fData.porc_agua_corporal = parseFloat(((parseFloat(vm.fData.kg_agua_corporal) * 100) / parseFloat(vm.fData.peso)).toFixed(2));
        }

        if(value == 'porc_grasa_visceral'){
          vm.fData.kg_grasa_visceral = parseFloat(((parseFloat(vm.fData.peso) * parseFloat(vm.fData.porc_grasa_visceral)) / 100).toFixed(2));
        }

        if(value == 'kg_grasa_visceral'){
          vm.fData.porc_grasa_visceral = parseFloat(((parseFloat(vm.fData.kg_grasa_visceral) * 100) / parseFloat(vm.fData.peso)).toFixed(2));
        }
      }
    }

    vm.calcularIndicadores = function(){
      //redondear peso
      vm.fData.peso = parseFloat((vm.fData.peso * 1).toFixed(2));
      //IMC peso / (estatura en mtr al cuadrado)
      vm.fData.imc = (vm.fData.peso / ((vm.cita.cliente.estatura/100) * (vm.cita.cliente.estatura/100))).toFixed(2);

      //Peso Ideal = 0,75 (altura en cm – 150) + 50
      vm.fData.pesoIdeal = ((0.75 * (vm.cita.cliente.estatura - 150)) + 50).toFixed(2);

      if(vm.cita.cliente.sexo == 'M'){
        //Hombres TMB = (10 x peso en kg) + (6,25 × altura en cm) – (5 × edad en años) + 5
        vm.fData.metabolismo = ((10 * vm.fData.peso) + (6.25 * vm.cita.cliente.estatura) - (5 * vm.cita.cliente.edad) + 5).toFixed(2);
      }

      if(vm.cita.cliente.sexo == 'F'){
        //Mujeres TMB = (10 x peso en kg) + (6,25 × altura en cm) – (5 × edad en años) – 161
        vm.fData.metabolismo = ((10 * vm.fData.peso) + (6.25 * vm.cita.cliente.estatura) - (5 * vm.cita.cliente.edad) - 161).toFixed(2);
      }

      if(vm.fData.imc < 18.5){
        vm.fData.porcFlecha1 = 9;
        vm.fData.colorImc = '#3A6FFF';
      }else if(vm.fData.imc >= 18.5 && vm.fData.imc <= 24.9){
        vm.fData.porcFlecha1 = 25;
        vm.fData.colorImc = '#49C45B';
      }else if(vm.fData.imc >= 25 && vm.fData.imc <= 29.9){
        vm.fData.porcFlecha1 = 42;
        vm.fData.colorImc = '#FFFD43';
      }else if(vm.fData.imc >= 30 && vm.fData.imc <= 34.9){
        vm.fData.porcFlecha1 = 58;
        vm.fData.colorImc = '#FF985B';
      }else if(vm.fData.imc >= 35 && vm.fData.imc <= 39.9){
        vm.fData.porcFlecha1 = 75;
        vm.fData.colorImc = '#FF4747';
      }else if(vm.fData.imc >= 40){
        vm.fData.porcFlecha1 = 91;
        vm.fData.colorImc = '#D63235';
      }

      if(vm.fData.porc_masa_grasa > 59){
        vm.fData.porcFlecha2 = 98;
      }else{
        vm.fData.porcFlecha2 = parseInt((vm.fData.porc_masa_grasa * 100)/60);
      }

      vm.fData.porcFlecha3 = parseInt((vm.fData.puntaje_grasa_visceral * 100)/20);
    }

    vm.btnRegistrarConsulta = function(){
      pageLoading.start('Registrando Consulta...');
      var datos={
        cita:vm.cita,
        consulta:vm.fData
      };

      ConsultasServices.sRegistrarConsulta(datos).then(function(rpta){
        // var openedToasts = [];
        if(rpta.flag == 1){ 
          vm.fData.idatencion = rpta.idatencion;
          vm.callback();
          vm.changePestania(3);      
          var pTitle = 'OK!';
          var pType = 'success';
        }else if( rpta.flag == 0 ){
          var pTitle = 'Advertencia!';
          var pType = 'warning';  
        }
        pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 }); 
        pageLoading.stop();
      });
    }

    vm.btnActualizarConsulta = function(){
      pageLoading.start('Actualizando Consulta...');
      var datos={
        cita:vm.cita,
        consulta:vm.fData
      };

      ConsultasServices.sActualizarConsulta(datos).then(function(rpta){
        // var openedToasts = [];
        if(rpta.flag == 1){ 
          if( vm.origen == 'citas' ){
            vm.callback();
            vm.changePestania(3);
          }else{
            vm.callback(vm.cita.cliente);
            $scope.changeViewPaciente(true);
            $scope.changeViewConsulta(false);
          }       
          var pTitle = 'OK!';
          var pType = 'success';
        }else if( rpta.flag == 0 ){
          var pTitle = 'Advertencia!';
          var pType = 'warning';  
        }
        pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
        pageLoading.stop();
      });
    }

    vm.btnGeneraPlan = function(){
      console.log(vm.cita,'vm.citafffffffffff',vm.fData);
      vm.fData.cita = vm.cita;

      $scope.changeViewPlan(true,vm.fData);
    }

    vm.btnImprimirConsulta = function(){
      var arrParams = {
        titulo: 'CONSULTA',
        datos:{
          consulta: vm.fData,
          salida: 'pdf',
          tituloAbv: 'Consulta',
          titulo: 'Consulta'
        },
        envio_correo: 'si',
        metodo: 'php',
        url: angular.patchURLCI + "Consulta/imprimir_consulta"
      }
      ModalReporteFactory.getPopupReporte(arrParams);
    }

    vm.btnImprimirPlan = function(){
      var arrParams = {
        titulo: 'PLAN ALIMENTARIO',
        datos:{
          cita:vm.cita,
          consulta:vm.fData,
          salida: 'pdf',
          tituloAbv: 'Plan Alimentario',
          titulo: 'Plan Alimentario'
        },
        metodo: 'php',
        envio_correo: 'si',
        url: angular.patchURLCI + "PlanAlimentario/generar_pdf_plan"
      }
      ModalReporteFactory.getPopupReporte(arrParams);
    }

    vm.btnEnviarPlan = function(){
      pageLoading.start('Enviando Plan Alimentario...');
      var datos = {
        cita:vm.cita,
        consulta:vm.fData,
        salida: 'correo',
        emails: vm.emails,
      }
      PlanAlimentarioServices.sGenerarPdfPlan(datos).then(function(rpta){
        if(rpta.flag == 1){      
          var pTitle = 'OK!';
          var pType = 'success';
        }else if( rpta.flag == 0 ){
          var pTitle = 'Advertencia!';
          var pType = 'warning';  
        }
        pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 }); 
        pageLoading.stop();
      });
    }

    vm.actualizarConsulta = function(idatencion){
      var datos = { atencion:{
                            idatencion:idatencion
                          }
                  };

      ConsultasServices.sCargarConsulta(datos).then(function(rpta){
        if(rpta.flag==1){          
          vm.fData = rpta.datos;
          vm.fData.fecha_atencion = moment(vm.fData.fecha_atencion).toDate();
          vm.fData.cita = vm.cita;
          /*vm.fData.kg_masa_grasa = parseFloat(((parseFloat(vm.fData.peso) * parseFloat(vm.fData.porc_masa_grasa)) / 100).toFixed(2));
          vm.fData.kg_masa_libre = parseFloat(((parseFloat(vm.fData.peso) * parseFloat(vm.fData.porc_masa_libre)) / 100).toFixed(2));
          */
        }
      });
    }
  }
  function ConsultasServices($http, $q, handle) {
    return({
        sRegistrarConsulta: sRegistrarConsulta,
        sActualizarConsulta:sActualizarConsulta,
        sAnularConsulta : sAnularConsulta,
        sCargarConsulta: sCargarConsulta,
        sCargarConsultasPaciente: sCargarConsultasPaciente,
        sGenerarPDFConsulta: sGenerarPDFConsulta
    });
    function sRegistrarConsulta(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Consulta/registrar_consulta",
            data : datos
      });
      return (request.then(handle.success,handle.error));
    }

    function sActualizarConsulta(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Consulta/actualizar_consulta",
            data : datos
      });
      return (request.then(handle.success,handle.error));
    }

    function sAnularConsulta(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Consulta/anular_consulta",
            data : datos
      });
      return (request.then(handle.success,handle.error));
    }
    function sCargarConsulta(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Consulta/cargar_consulta",
            data : datos
      });
      return (request.then(handle.success,handle.error));
    }
    function sCargarConsultasPaciente(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Consulta/listar_consultas_paciente",
            data : datos
      });
      return (request.then(handle.success,handle.error));
    }
    function sGenerarPDFConsulta(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Consulta/imprimir_consulta",
            data : datos
      });
      return (request.then(handle.success,handle.error));
    }
  }
})();
