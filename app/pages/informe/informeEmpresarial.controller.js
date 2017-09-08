(function() {
  'use strict';

  angular
    .module('minotaur')
    .controller('InformeEmpresarialController', InformeEmpresarialController)
    .service('InformeEmpresarialServices', InformeEmpresarialServices);

  /** @ngInject */
  function InformeEmpresarialController($scope, $uibModal, $timeout, $filter, InformeEmpresarialServices, EmpresaServices, pinesNotifications, pageLoading) { 

    var vm = this; 
    vm.fData = {};
    vm.fData.informe = {};
    vm.fParam = {};
    vm.fParam.infoVisible = false;
    vm.fParam.inicio = moment().format('01-MM-YYYY');
    vm.fParam.fin = moment().format('DD-MM-YYYY');
    // console.log(vm.fParam.fin,'vm.fParam.fin'); 
    // LISTA DE EMPRESAS
    EmpresaServices.sListarEmpresaCbo().then(function (rpta) {
      vm.fData.listaEmpresas = angular.copy(rpta.datos);
      vm.fData.listaEmpresas.splice(0,0,{ id : '', descripcion:'--Seleccione una empresa--'}); 
      vm.fParam.empresa = vm.fData.listaEmpresas[0];
    }); 
    vm.fData.informe.chartConfigPA = { 
      chart: { 
          type: 'pie',
          height: 250,

      },
      title: {
          text: 'PACIENTES POR GÉNERO'
      },
      tooltip: {
          pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>' 
      },
      plotOptions: {
        pie: {
            allowPointSelect: true,
            cursor: 'pointer',
            dataLabels: {
              enabled: false
            },
            showInLegend: true
        }
      },
      legend: {
        labelFormat: '{name} ( {y} )' 
      },
      series: [{ 
        name: 'Sexo.',
        colorByPoint: true,
        data: []
      }]
    }; 
    vm.fData.informe.chartConfigEdad = { 
      chart: { 
          type: 'pie',
          height: 250,

      },
      title: {
          text: 'PACIENTES POR EDAD'
      },
      tooltip: {
          pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>' 
      },
      plotOptions: {
        pie: {
            allowPointSelect: true,
            cursor: 'pointer',
            dataLabels: {
              enabled: false
            },
            showInLegend: true
        }
      },
      legend: {
        labelFormat: '{name} ( {y} )' 
      },
      series: [{ 
        name: 'Grupo Etáreo.',
        colorByPoint: true,
        data: []
      }]
    }; 
    // DX POR IMC 
    vm.fData.informe.chartConfigPesoIMC = { 
      chart: { 
          type: 'column',
          height: 350
      },
      title: {
          text: 'Dx. Nutricional.' 
      },
      subtitle: {
        text: 'Según IMC'
      },
      xAxis: {
        type: 'category',
        labels: {
            rotation: -45
        }
      },
      yAxis: {
        min: 0,
        title: {
            text: 'Cant. de Consultas' 
        }
      },
      legend: {
        enabled: false
      },
      tooltip: {
        pointFormat: '<b>{point.y} </b> consultas' 
      }, 
      series: [{ 
        name: 'Diagnóstico según IMC. ',
        colorByPoint: true,
        data: [],
        dataLabels: {
            enabled: true,
            rotation: -90,
            color: '#000000',
            align: 'right',
            format: '{point.y}'
        }
      }]
    }; 
    vm.fData.informe.chartConfigEdadPesoIMC = { 
      chart: { 
          type: 'column',
          height: 350
      },
      title: {
          text: 'Edad & Dx. Nutricional.' 
      },
      subtitle: {
        text: 'Según IMC'
      },
      xAxis: {
        categories: [
            'JOVENES',
            'ADULTOS',
            'ADULTOS MAYORES' 
        ],
        crosshair: true
      },
      yAxis: {
          min: 0,
          title: {
              text: 'Cant. de Consultas.'
          }
      },
      tooltip: { 
        headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
        pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
            '<td style="padding:0"><b>{point.y} u.</b></td></tr>',
        footerFormat: '</table>',
        shared: true,
        useHTML: true
      },
      plotOptions: {
        column: {
            pointPadding: 0.2,
            borderWidth: 0
        }
      },
      series: [] 
    }; 
    vm.fData.informe.chartConfigSexoPesoIMC = { 
      chart: { 
          type: 'column',
          height: 350
      },
      title: {
          text: 'Sexo & Dx. Nutricional.' 
      },
      subtitle: {
        text: 'Según IMC'
      },
      xAxis: {
        categories: [
            'MASCULINO',
            'FEMENINO'
        ],
        crosshair: true
      },
      yAxis: {
          min: 0,
          title: {
              text: 'Cant. de Consultas.'
          }
      },
      tooltip: { 
        headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
        pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
            '<td style="padding:0"><b>{point.y} u.</b></td></tr>',
        footerFormat: '</table>',
        shared: true,
        useHTML: true
      },
      plotOptions: {
        column: {
            pointPadding: 0.2,
            borderWidth: 0
        }
      },
      series: [] 
    }; 
    // DX POR INDICE DE GRASA VISCERAL 
    vm.fData.informe.chartConfigGrasaVisceral = { 
      chart: { 
          type: 'column',
          height: 350
      },
      title: {
          text: 'Dx. Nutricional.' 
      },
      subtitle: {
        text: 'Según Índice de Grasa Visceral.'
      },
      xAxis: {
        type: 'category',
        labels: {
            rotation: -45
        }
      },
      yAxis: {
        min: 0,
        title: {
            text: 'Cant. de Consultas' 
        }
      },
      legend: {
        enabled: false
      },
      tooltip: {
        pointFormat: '<b>{point.y} </b> consultas' 
      }, 
      series: [{ 
        name: 'Diagnóstico según Grasa Visceral. ',
        colorByPoint: true,
        data: [],
        dataLabels: {
            enabled: true,
            rotation: -90,
            color: '#000000',
            align: 'right',
            format: '{point.y}'
        }
      }]
    }; 
    vm.fData.informe.chartConfigEdadGrasaVisceral = { 
      chart: { 
          type: 'column',
          height: 350
      },
      title: {
          text: 'Edad & Dx. Nutricional.' 
      },
      subtitle: {
        text: 'Según Índice de Grasa Visceral.'
      },
      xAxis: {
        categories: [
            'JOVENES',
            'ADULTOS',
            'ADULTOS MAYORES' 
        ],
        crosshair: true
      },
      yAxis: {
          min: 0,
          title: {
              text: 'Cant. de Consultas.'
          }
      },
      tooltip: { 
        headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
        pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
            '<td style="padding:0"><b>{point.y} u.</b></td></tr>',
        footerFormat: '</table>',
        shared: true,
        useHTML: true
      },
      plotOptions: {
        column: {
            pointPadding: 0.2,
            borderWidth: 0
        }
      },
      series: [] 
    }; 
    vm.fData.informe.chartConfigSexoGrasaVisceral = { 
      chart: { 
          type: 'column',
          height: 350
      },
      title: {
          text: 'Sexo & Dx. Nutricional.' 
      },
      subtitle: {
        text: 'Según Índice de Grasa Visceral.'
      },
      xAxis: {
        categories: [
            'MASCULINO',
            'FEMENINO'
        ],
        crosshair: true
      },
      yAxis: {
          min: 0,
          title: {
              text: 'Cant. de Consultas.'
          }
      },
      tooltip: { 
        headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
        pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
            '<td style="padding:0"><b>{point.y} u.</b></td></tr>',
        footerFormat: '</table>',
        shared: true,
        useHTML: true
      },
      plotOptions: {
        column: {
            pointPadding: 0.2,
            borderWidth: 0
        }
      },
      series: [] 
    }; 
    // DX POR PORC. DE GRASA CORPORAL 
    vm.fData.informe.chartConfigPorcGrasaCorporal = { 
      chart: { 
          type: 'column',
          height: 350
      },
      title: {
          text: 'Dx. Nutricional.' 
      },
      subtitle: {
        text: 'Según % de Grasa Corporal.'
      },
      xAxis: {
        type: 'category',
        labels: {
            rotation: -45
        }
      },
      yAxis: {
        min: 0,
        title: {
            text: 'Cant. de Consultas' 
        }
      },
      legend: {
        enabled: false
      },
      tooltip: {
        pointFormat: '<b>{point.y} </b> consultas' 
      }, 
      series: [{ 
        name: 'Diagnóstico según % de Grasa Corporal.. ',
        colorByPoint: true,
        data: [],
        dataLabels: {
            enabled: true,
            rotation: -90,
            color: '#000000',
            align: 'right',
            format: '{point.y}'
        }
      }]
    }; 
    vm.fData.informe.chartConfigEdadPorcGrasaCorporal = { 
      chart: { 
          type: 'column',
          height: 350
      },
      title: {
          text: 'Edad & Dx. Nutricional.' 
      },
      subtitle: {
        text: 'Según % de Grasa Corporal.'
      },
      xAxis: {
        categories: [
            'JOVENES',
            'ADULTOS',
            'ADULTOS MAYORES' 
        ],
        crosshair: true
      },
      yAxis: {
          min: 0,
          title: {
              text: 'Cant. de Consultas.'
          }
      },
      tooltip: { 
        headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
        pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
            '<td style="padding:0"><b>{point.y} u.</b></td></tr>',
        footerFormat: '</table>',
        shared: true,
        useHTML: true
      },
      plotOptions: {
        column: {
            pointPadding: 0.2,
            borderWidth: 0
        }
      },
      series: [] 
    }; 
    vm.fData.informe.chartConfigSexoPorcGrasaCorporal = { 
      chart: { 
          type: 'column',
          height: 350
      },
      title: {
          text: 'Sexo & Dx. Nutricional.' 
      },
      subtitle: {
        text: 'Según % de Grasa Corporal.' 
      },
      xAxis: {
        categories: [
            'MASCULINO',
            'FEMENINO'
        ],
        crosshair: true
      },
      yAxis: {
          min: 0,
          title: {
              text: 'Cant. de Consultas.'
          }
      },
      tooltip: { 
        headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
        pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
            '<td style="padding:0"><b>{point.y} u.</b></td></tr>',
        footerFormat: '</table>',
        shared: true,
        useHTML: true
      },
      plotOptions: {
        column: {
            pointPadding: 0.2,
            borderWidth: 0
        }
      },
      series: [] 
    }; 
    // DX POR PERÍMETRO DE CINTURA - RIESGO CARDIOVASCULAR 
    vm.fData.informe.chartConfigRiesgoCardio = { 
      chart: { 
          type: 'column',
          height: 350
      },
      title: {
          text: 'Dx. Nutricional.' 
      },
      subtitle: {
        text: 'Según Perímetro de Cintura.'
      },
      xAxis: {
        type: 'category',
        labels: {
            rotation: -45
        }
      },
      yAxis: {
        min: 0,
        title: {
            text: 'Cant. de Consultas' 
        }
      },
      legend: {
        enabled: false
      },
      tooltip: {
        pointFormat: '<b>{point.y} </b> consultas' 
      }, 
      series: [{ 
        name: 'Diagnóstico según Perímetro de Cintura.. ',
        colorByPoint: true,
        data: [],
        dataLabels: {
            enabled: true,
            rotation: -90,
            color: '#000000',
            align: 'right',
            format: '{point.y}'
        }
      }]
    }; 
    vm.fData.informe.chartConfigEdadRiesgoCardio = { 
      chart: { 
          type: 'column',
          height: 350
      },
      title: {
          text: 'Edad & Dx. Nutricional.' 
      },
      subtitle: {
        text: 'Según Perímetro de Cintura.'
      },
      xAxis: {
        categories: [
            'JOVENES',
            'ADULTOS',
            'ADULTOS MAYORES' 
        ],
        crosshair: true
      },
      yAxis: {
          min: 0,
          title: {
              text: 'Cant. de Consultas.'
          }
      },
      tooltip: { 
        headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
        pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
            '<td style="padding:0"><b>{point.y} u.</b></td></tr>',
        footerFormat: '</table>',
        shared: true,
        useHTML: true
      },
      plotOptions: {
        column: {
            pointPadding: 0.2,
            borderWidth: 0
        }
      },
      series: [] 
    }; 
    vm.fData.informe.chartConfigSexoRiesgoCardio = { 
      chart: { 
          type: 'column',
          height: 350
      },
      title: {
          text: 'Sexo & Dx. Nutricional.' 
      },
      subtitle: {
        text: 'Según Perímetro de Cintura.' 
      },
      xAxis: {
        categories: [
            'MASCULINO',
            'FEMENINO'
        ],
        crosshair: true
      },
      yAxis: {
          min: 0,
          title: {
              text: 'Cant. de Consultas.'
          }
      },
      tooltip: { 
        headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
        pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
            '<td style="padding:0"><b>{point.y} u.</b></td></tr>',
        footerFormat: '</table>',
        shared: true,
        useHTML: true
      },
      plotOptions: {
        column: {
            pointPadding: 0.2,
            borderWidth: 0
        }
      },
      series: [] 
    }; 
    // DX POR % DE MASA MUSCULAR 
    vm.fData.informe.chartConfigPorcMasaMuscular = { 
      chart: { 
          type: 'column',
          height: 350
      },
      title: {
          text: 'Dx. Nutricional.' 
      },
      subtitle: {
        text: 'Según % de Masa Muscular.'
      },
      xAxis: {
        type: 'category',
        labels: {
            rotation: -45
        }
      },
      yAxis: {
        min: 0,
        title: {
            text: 'Cant. de Consultas' 
        }
      },
      legend: {
        enabled: false
      },
      tooltip: {
        pointFormat: '<b>{point.y} </b> consultas' 
      }, 
      series: [{ 
        name: 'Diagnóstico según % de Masa Muscular',
        colorByPoint: true,
        data: [],
        dataLabels: {
            enabled: true,
            rotation: -90,
            color: '#000000',
            align: 'right',
            format: '{point.y}'
        }
      }]
    }; 
    vm.fData.informe.chartConfigEdadPorcMasaMuscular = { 
      chart: { 
          type: 'column',
          height: 350
      },
      title: {
          text: 'Edad & Dx. Nutricional.' 
      },
      subtitle: {
        text: 'Según % de Masa Muscular.'
      },
      xAxis: {
        categories: [
            'JOVENES',
            'ADULTOS',
            'ADULTOS MAYORES' 
        ],
        crosshair: true
      },
      yAxis: {
          min: 0,
          title: {
              text: 'Cant. de Consultas.'
          }
      },
      tooltip: { 
        headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
        pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
            '<td style="padding:0"><b>{point.y} u.</b></td></tr>',
        footerFormat: '</table>',
        shared: true,
        useHTML: true
      },
      plotOptions: {
        column: {
            pointPadding: 0.2,
            borderWidth: 0
        }
      },
      series: [] 
    }; 
    vm.fData.informe.chartConfigSexoPorcMasaMuscular = { 
      chart: { 
          type: 'column',
          height: 350
      },
      title: {
          text: 'Sexo & Dx. Nutricional.' 
      },
      subtitle: {
        text: 'Según % de Masa Muscular.'
      },
      xAxis: {
        categories: [
            'MASCULINO',
            'FEMENINO'
        ],
        crosshair: true
      },
      yAxis: {
          min: 0,
          title: {
              text: 'Cant. de Consultas.'
          }
      },
      tooltip: { 
        headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
        pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
            '<td style="padding:0"><b>{point.y} u.</b></td></tr>',
        footerFormat: '</table>',
        shared: true,
        useHTML: true
      },
      plotOptions: {
        column: {
            pointPadding: 0.2,
            borderWidth: 0
        }
      },
      series: [] 
    }; 

    vm.fData.informe.chartConfigPPS = { 
      chart: { 
          type: 'pie',
          height: 250,

      },
      title: {
          text: 'PESO PERDIDO POR GÉNERO'
      },
      tooltip: {
          pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>' 
      },
      plotOptions: {
        pie: {
            allowPointSelect: true,
            cursor: 'pointer',
            dataLabels: {
              enabled: false
            },
            showInLegend: true
        }
      },
      legend: {
        labelFormat: '{name} ( {y} Kg.)' 
      },
      series: [{ 
        name: 'Género.',
        colorByPoint: true,
        data: []
      }]
    };
    vm.fData.informe.chartConfigPPE = { 
      chart: { 
          type: 'pie',
          height: 250,

      },
      title: {
          text: 'PESO PERDIDO POR EDAD' 
      },
      tooltip: {
          pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>' 
      },
      plotOptions: {
        pie: {
            allowPointSelect: true,
            cursor: 'pointer',
            dataLabels: {
              enabled: false
            },
            showInLegend: true
        }
      },
      legend: {
        labelFormat: '{name} ( {y} Kg.)' 
      },
      series: [{ 
        name: 'Edad.',
        colorByPoint: true,
        data: []
      }]
    };
    vm.fData.informe.chartConfigPGS = { 
      chart: { 
          type: 'pie',
          height: 250,

      },
      title: {
          text: 'GRASA PERDIDA POR GÉNERO'
      },
      tooltip: {
          pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>' 
      },
      plotOptions: {
        pie: {
            allowPointSelect: true,
            cursor: 'pointer',
            dataLabels: {
              enabled: false
            },
            showInLegend: true
        }
      },
      legend: {
        labelFormat: '{name} ( {y} Kg.)' 
      },
      series: [{ 
        name: 'Género.',
        colorByPoint: true,
        data: []
      }]
    };
    vm.fData.informe.chartConfigPGE = { 
      chart: { 
          type: 'pie',
          height: 250,

      },
      title: {
          text: 'GRASA PERDIDA POR EDAD' 
      },
      tooltip: {
          pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>' 
      },
      plotOptions: {
        pie: {
            allowPointSelect: true,
            cursor: 'pointer',
            dataLabels: {
              enabled: false
            },
            showInLegend: true
        }
      },
      legend: {
        labelFormat: '{name} ( {y} Kg.)' 
      },
      series: [{ 
        name: 'Edad.',
        colorByPoint: true,
        data: []
      }]
    };
    vm.fParam.generarInformeEmpresarial = function() { 
      pageLoading.start('Cargando datos...');
      InformeEmpresarialServices.sListarInformeEmpresa(vm.fParam).then(function (rpta) { 
        if( rpta.flag == 1 ){
          vm.fParam.infoVisible = true;
          vm.fData.informe.pac_atendidos = angular.copy(rpta.datos.pac_atendidos);
          vm.fData.informe.atenciones_realizadas = angular.copy(rpta.datos.atenciones_realizadas);
          vm.fData.informe.chartConfigPA.series[0].data = angular.copy(rpta.datos.pac_sexo_graph); 
          vm.fData.informe.chartConfigEdad.series[0].data = angular.copy(rpta.datos.pac_edad_graph); 
          // IMC 
          vm.fData.informe.chartConfigPesoIMC.series[0].data = angular.copy(rpta.datos.pac_peso_graph); 
          vm.fData.informe.chartConfigEdadPesoIMC.series = angular.copy(rpta.datos.pac_edad_peso_graph); 
          vm.fData.informe.chartConfigSexoPesoIMC.series = angular.copy(rpta.datos.pac_sexo_peso_graph); 
          // INDICE DE GRASA VISCERAL 
          vm.fData.informe.chartConfigGrasaVisceral.series[0].data = angular.copy(rpta.datos.pac_grasa_visceral_graph); 
          vm.fData.informe.chartConfigEdadGrasaVisceral.series = angular.copy(rpta.datos.pac_edad_grasa_visceral_graph); 
          vm.fData.informe.chartConfigSexoGrasaVisceral.series = angular.copy(rpta.datos.pac_sexo_grasa_visceral_graph); 
          // % MASA MUSCULAR 
          vm.fData.informe.chartConfigPorcMasaMuscular.series[0].data = angular.copy(rpta.datos.pac_porc_masa_muscular_graph); 
          vm.fData.informe.chartConfigEdadPorcMasaMuscular.series = angular.copy(rpta.datos.pac_edad_porc_masa_muscular_graph); 
          vm.fData.informe.chartConfigSexoPorcMasaMuscular.series = angular.copy(rpta.datos.pac_sexo_porc_masa_muscular_graph); 
          // % GRASA CORPORAL 
          vm.fData.informe.chartConfigPorcGrasaCorporal.series[0].data = angular.copy(rpta.datos.pac_porc_grasa_corporal_graph); 
          vm.fData.informe.chartConfigEdadPorcGrasaCorporal.series = angular.copy(rpta.datos.pac_edad_porc_grasa_corporal_graph); 
          vm.fData.informe.chartConfigSexoPorcGrasaCorporal.series = angular.copy(rpta.datos.pac_sexo_porc_grasa_corporal_graph); 
          // PERÍMETRO DE CINTURA
          vm.fData.informe.chartConfigRiesgoCardio.series[0].data = angular.copy(rpta.datos.pac_riesgo_cardio_graph); 
          vm.fData.informe.chartConfigEdadRiesgoCardio.series = angular.copy(rpta.datos.pac_edad_riesgo_cardio_graph); 
          vm.fData.informe.chartConfigSexoRiesgoCardio.series = angular.copy(rpta.datos.pac_sexo_riesgo_cardio_graph); 
          // INFORME DE PÉRDIDA DE PESO 
          vm.fData.informe.peso_perdido = angular.copy(rpta.datos.peso_perdido); 
          vm.fData.informe.chartConfigPPS.series[0].data = angular.copy(rpta.datos.peso_perdido_sexo_graph); 
          vm.fData.informe.chartConfigPPE.series[0].data = angular.copy(rpta.datos.peso_perdido_edad_graph); 
          // INFORME DE PÉRDIDA DE GRASA 
          vm.fData.informe.grasa_perdida = angular.copy(rpta.datos.grasa_perdida);
          vm.fData.informe.chartConfigPGS.series[0].data = angular.copy(rpta.datos.grasa_perdida_sexo_graph); 
          vm.fData.informe.chartConfigPGE.series[0].data = angular.copy(rpta.datos.grasa_perdida_edad_graph); 
        }else{
          vm.fParam.infoVisible = false;
        }
        pageLoading.stop(); 
      }); 
    }
  }
  function InformeEmpresarialServices($http, $q, handle) {
    return({
        sListarInformeEmpresa: sListarInformeEmpresa
    });
    function sListarInformeEmpresa(pDatos) {
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url :  angular.patchURLCI + "InformeEmpresarial/listar_informe_empresarial",
            data : datos
      });
      return (request.then( handle.success,handle.error ));
    }            
  }
})();
