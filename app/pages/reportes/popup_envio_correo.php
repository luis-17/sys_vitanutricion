<div class="modal-header">
  <h4 class="modal-title">{{ titleModalReporteEmail }}</h4>
</div>
<div class="modal-body">
	<section class="tile-body">
		<form name="formEmail" role="form" novalidate class="form-validation">
		    <div class="row"> 
		    	<div class="form-group col-md-12">
                  <label for="name" class="control-label minotaur-label" style="width: 100%;">Correos
                    <span class="text-red">*</span>:
                  </label>
                  <input type="text" ng-model="fEnvio.emails" required class="form-control">
                </div> 
		    </div>
		</form>
	</section>
</div>
<div class="modal-footer">
  <button class="btn btn-warning btn-ef btn-ef-4 btn-ef-4c" ng-click="detCancel();"><i class="fa fa-arrow-left"></i> Salir </button>
  <button class="btn btn-success btn-ef btn-ef-3 btn-ef-3c" ng-disabled="formEmail.$invalid" ng-click="envioCorreoExec();"><i class="fa fa-arrow-right"></i> Enviar </button>
</div>