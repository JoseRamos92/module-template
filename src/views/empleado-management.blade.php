@extends('layouts.base')

@section('container')
{!! Form::hidden('rh-em-new-action', null, array('id' => 'rh-em-new-action')) !!}
{!! Form::hidden('rh-em-edit-action', null, array('id' => 'rh-em-edit-action', 'data-content' => Lang::get('module::app.editHelpText'))) !!}
{!! Form::hidden('rh-em-remove-action', null, array('id' => 'rh-em-remove-action', 'data-content' => Lang::get('module::app.editHelpText'))) !!}
{!! Form::button('', array('id' => 'rh-em-btn-edit-helper', 'class' => 'hidden')) !!}
{!! Form::button('', array('id' => 'rh-em-btn-delete-helper', 'class' => 'hidden')) !!}
<style></style>

<script type='text/javascript'>
	//Falta agregar  codigo para quitar tooltip
	//For grids with multiselect enabled
	function rhEmOnSelectRowEvent(id)
	{
		var selRowIds = $('#rh-em-grid').jqGrid('getGridParam', 'selarrrow');

		if(selRowIds.length == 0)
		{
			$('#rh-em-btn-group-2').disabledButtonGroup();
			cleanJournals('rh-em-');
		}
		else if(selRowIds.length == 1)
		{
			$('#rh-em-btn-group-2').enableButtonGroup();
			cleanJournals('rh-em-');
			getAppJournals('rh-em-','firstPage', $('#rh-em-grid').getSelectedRowId());
		}
		else if(selRowIds.length > 1)
		{
			$('#rh-em-btn-group-2').disabledButtonGroup();
			$('#rh-em-btn-delete').removeAttr('disabled');
			cleanJournals('rh-em-');
		}
	}

	/*
	//For grids with multiselect disabled
	function rhEmOnSelectRowEvent()
	{
		var id = $('#rh-em-grid').getSelectedRowId('module_app_id');

		getAppJournals('rh-em-', 'firstPage', id);

		$('#rh-em-btn-group-2').enableButtonGroup();
	}
	*/

	$(document).ready(function()
	{
		$('.rh-em-btn-tooltip').tooltip();

		$('#rh-em-form').jqMgVal('addFormFieldsValidations');

		$('#rh-em-grid-section').on('shown.bs.collapse', function ()
		{
			$('#rh-em-btn-refresh').click();
		});

		$('#rh-em-journals-section').on('hidden.bs.collapse', function ()
		{
			$('#rh-em-form-section').collapse('show');//aqui
		});

		$('#rh-em-form-section').on('shown.bs.collapse', function ()//evento para cuando ya haya cargado
		{
			$('#rh-em-name').focus();//focus en textboxt
		});

		$('#rh-em-form-section').on('hidden.bs.collapse', function ()
		{
			$('#rh-em-grid-section').collapse('show');

			$('#rh-em-journals-section').collapse('show');
		});

		$('#rh-em-btn-new').click(function()
		{
			if($(this).hasAttr('disabled'))
			{
				return;
			}

			$('#rh-em-btn-toolbar').disabledButtonGroup();//oculta el grupo de botones nuevo, bug, hola,actualizar, exportar
			$('#rh-em-btn-group-3').enableButtonGroup();//habilita el grupo de bonotes guardar y regresar
			$('#rh-em-form-new-title').removeClass('hidden');
			$('#rh-em-grid-section').collapse('hide');
			$('#rh-em-journals-section').collapse('hide');
			$('.rh-em-btn-tooltip').tooltip('hide');
		});

		$('#rh-em-btn-bug').click(function()
		{
			if($(this).hasAttr('disabled'))
			{
				return;
			}
				alert("Hola");
				$('.rh-em-btn-tooltip').tooltip('hide');
		});

		$('#rh-em-btn-refresh').click(function()
		{
			$('.rh-em-btn-tooltip').tooltip('hide');
			$('#rh-em-grid').trigger('reloadGrid');
			cleanJournals('rh-em-');
		});

		$('#rh-em-btn-export-xls').click(function()
		{
				$('#rh-em-gridXlsButton').click();
		});

		$('#rh-em-btn-export-csv').click(function()
		{
				$('#rh-em-gridCsvButton').click();
		});

		$('#rh-em-btn-edit').click(function()
		{
			var rowData;

			$('#rh-em-btn-toolbar').disabledButtonGroup();
			$('#rh-em-btn-group-3').enableButtonGroup();
			$('#rh-em-form-edit-title').removeClass('hidden');

			rowData = $('#rh-em-grid').getRowData($('#rh-em-grid').jqGrid('getGridParam', 'selrow'));

			populateFormFields(rowData);

			$('#rh-em-grid-section').collapse('hide');
			$('#rh-em-journals-section').collapse('hide');
			$('.rh-em-btn-tooltip').tooltip('hide');
		});

		$('#rh-em-btn-delete').click(function()
		{
			var rowData;

			if($(this).hasAttr('disabled'))
			{
				return;
			}

			rowData = $('#rh-em-grid').getRowData($('#rh-em-grid').jqGrid('getGridParam', 'selrow'));

			$('#rh-em-delete-message').html($('#rh-em-delete-message').attr('data-default-label').replace(':field0', rowData.field0));

			$('#rh-em-modal-delete').modal('show');
		});

		$('#rh-em-btn-modal-delete').click(function()
		{
			//For grids with multiselect enabled
			var id = $('#rh-em-grid').getSelectedRowsIdCell('module_app_id');

			if(id.length == 0)
			{
				return;
			}

			//For grids with multiselect disabled
			// var id = $('#rh-em-grid').getSelectedRowId('module_app_id');

			$.ajax(
			{
				type: 'POST',
				data: JSON.stringify({'_token':$('#app-token').val(), 'id':id}),
				dataType : 'json',
				url:  $('#rh-em-form').attr('action') + '/delete',
				error: function (jqXHR, textStatus, errorThrown)
				{
					handleServerExceptions(jqXHR, 'rh-em-btn-toolbar', false);
				},
				beforeSend:function()
				{
					$('#app-loader').removeClass('hidden');
					disabledAll();
				},
				success:function(json)
				{
					if(json.success)
					{
						$('#rh-em-btn-refresh').click();
						$('#rh-em-modal-delete').modal('hide');
						$('#rh-em-btn-toolbar').showAlertAfterElement('alert-success alert-custom',json.success, 5000);
					}

					$('#app-loader').addClass('hidden');
					enableAll();
				}
			});
		});

		$('#rh-em-btn-save').click(function()
		{
			var url = $('#rh-em-form').attr('action'), action = 'new';

			$('.rh-em-btn-tooltip').tooltip('hide');

			if(!$('#rh-em-form').jqMgVal('isFormValid'))
			{
				return;
			}

			if($('#rh-em-id').isEmpty())
			{
				url = url + '/create';
			}
			else
			{
				url = url + '/update';
				action = 'edit';
			}

			$.ajax(
			{
				type: 'POST',
				data: JSON.stringify($('#rh-em-form').formToObject('rh-em-')),
				dataType : 'json',
				url: url,
				error: function (jqXHR, textStatus, errorThrown)
				{
					handleServerExceptions(jqXHR, 'rh-em-form');
				},
				beforeSend:function()
				{
					$('#app-loader').removeClass('hidden');
					disabledAll();
				},
				success:function(json)
				{
					if(json.success)
					{
						$('#rh-em-btn-close').click();
					}
					else if(json.info)
					{
						$('#rh-em-form').showAlertAsFirstChild('alert-info', json.info);
					}

					$('#app-loader').addClass('hidden');
					enableAll();
				}
			});
		});

		$('#rh-em-btn-close').click(function()
		{
			if($(this).hasAttr('disabled'))
			{
				return;
			}

			$('#rh-em-btn-group-1').enableButtonGroup();
			$('#rh-em-btn-group-3').disabledButtonGroup();
			$('#rh-em-form-new-title').addClass('hidden');
			$('#rh-em-form-edit-title').addClass('hidden');
			$('#rh-em-grid').jqGrid('clearGridData');
			$('#rh-em-form').jqMgVal('clearForm');
			$('.rh-em-btn-tooltip').tooltip('hide');
			$('#rh-em-form-section').collapse('hide');
		});
	});

	$('#rh-em-btn-edit-helper').click(function()
  {
		showButtonHelper('rh-em-btn-close', 'rh-em-btn-group-2', $('#rh-em-edit-action').attr('data-content'));
  });

	$('#rh-em-btn-delete-helper').click(function()
  {
		showButtonHelper('rh-em-btn-close', 'rh-em-btn-group-2', $('#rh-em-delete-action').attr('data-content'));
  });

	if(!$('#rh-em-new-action').isEmpty())
	{
		$('#rh-em-btn-new').click();
	}

	// if(!$('#rh-em-bug-action').isEmpty())
	// {
	// 	$('#rh-em-btn-bug').click();
	// }

	if(!$('#rh-em-edit-action').isEmpty())
	{
		showButtonHelper('rh-em-btn-close', 'rh-em-btn-group-2', $('#rh-em-edit-action').attr('data-content'));
	}

	if(!$('#rh-em-delete-action').isEmpty())
	{
		showButtonHelper('rh-em-btn-close', 'rh-em-btn-group-2', $('#rh-em-delete-action').attr('data-content'));
	}
</script>

<div class="row">
	<div class="col-lg-12 col-md-12">
		<div id="rh-em-btn-toolbar" class="section-header btn-toolbar" role="toolbar">
			<div id="rh-em-btn-group-1" class="btn-group btn-group-app-toolbar">
				{!! Form::button('<i class="fa fa-plus"></i> ' . Lang::get('toolbar.new'), array('id' => 'rh-em-btn-new', 'class' => 'btn btn-success rh-em-btn-tooltip', 'data-container' => 'body', 'data-toggle' => 'tooltip', 'data-original-title' => Lang::get('decima-module::empleado-management.message'))) !!}
				{!! Form::button('<i class="fa fa-bug"></i> ' . Lang::get('decima-module::empleado-management.menuBug'), array('id' => 'rh-em-btn-bug', 'class' => 'btn btn-danger rh-em-btn-tooltip', 'data-container' => 'body', 'data-toggle' => 'tooltip', 'data-original-title' => Lang::get('decima-module::empleado-management.message'))) !!}
				{!! Form::button('<i class="fa fa-bug"></i> ' . Lang::get('decima-module::empleado-management.menubtnnew'), array('id' => 'rh-em-btn-bug', 'class' => 'btn btn-default rh-em-btn-tooltip', 'data-container' => 'body', 'data-toggle' => 'tooltip', 'data-original-title' => Lang::get('decima-module::empleado-management.message'))) !!}

				{!! Form::button('<i class="fa fa-refresh"></i> ' . Lang::get('toolbar.refresh'), array('id' => 'rh-em-btn-refresh', 'class' => 'btn btn-default rh-em-btn-tooltip', 'data-container' => 'body', 'data-toggle' => 'tooltip', 'data-original-title' => Lang::get('toolbar.refreshLongText'))) !!}
				<div class="btn-group">
					{!! Form::button('<i class="fa fa-share-square-o"></i> ' . Lang::get('toolbar.export') . ' <span class="caret"></span>', array('class' => 'btn btn-default dropdown-toggle', 'data-container' => 'body', 'data-toggle' => 'dropdown')) !!}
					<ul class="dropdown-menu">
         		<li><a id='rh-em-btn-export-xls' class="fake-link"><i class="fa fa-file-excel-o"></i> xls</a></li>
         		<li><a id='rh-em-btn-export-csv' class="fake-link"><i class="fa fa-file-text-o"></i> csv</a></li>
       		</ul>
				</div>
			</div>
			<div id="rh-em-btn-group-2" class="btn-group btn-group-app-toolbar">
				{!! Form::button('<i class="fa fa-edit"></i> ' . Lang::get('toolbar.edit'), array('id' => 'rh-em-btn-edit', 'class' => 'btn btn-default rh-em-btn-tooltip', 'data-container' => 'body', 'data-toggle' => 'tooltip', 'disabled' => '', 'data-original-title' => Lang::get('module::app.edit'))) !!}
				{!! Form::button('<i class="fa fa-minus"></i> ' . Lang::get('toolbar.delete'), array('id' => 'rh-em-btn-delete', 'class' => 'btn btn-default rh-em-btn-tooltip', 'data-container' => 'body', 'data-toggle' => 'tooltip', 'disabled' => '', 'data-original-title' => Lang::get('module::app.delete'))) !!}
			</div>
			<div id="rh-em-btn-group-3" class="btn-group btn-group-app-toolbar">
				{!! Form::button('<i class="fa fa-save"></i> ' . Lang::get('toolbar.save'), array('id' => 'rh-em-btn-save', 'class' => 'btn btn-default rh-em-btn-tooltip', 'data-container' => 'body', 'data-toggle' => 'tooltip', 'disabled' => '', 'data-original-title' => Lang::get('module::app.save'))) !!}
				{!! Form::button('<i class="fa fa-undo"></i> ' . Lang::get('toolbar.close'), array('id' => 'rh-em-btn-close', 'class' => 'btn btn-default rh-em-btn-tooltip', 'data-container' => 'body', 'data-toggle' => 'tooltip', 'disabled' => '', 'data-original-title' => Lang::get('toolbar.closeLongText'))) !!}
			</div>
		</div>
		<div id='rh-em-grid-section' class='app-grid collapse in' data-app-grid-id='rh-em-grid'>
			{!!
			GridRender::setGridId("rh-em-grid")
				->enablefilterToolbar(false, false)
				->hideXlsExporter()
  			->hideCsvExporter()
	    	->setGridOption('url',URL::to('module/category/app/grid-data'))
	    	->setGridOption('caption', Lang::get('module::app.gridTitle', array('user' => AuthManager::getLoggedUserFirstname())))
	    	->setGridOption('postData',array('_token' => Session::token()))
				->setGridEvent('onSelectRow', 'rhEmOnSelectRowEvent')
	    	->addColumn(array('index' => 'id', 'name' => 'module_app_id', 'hidden' => true))
	    	->addColumn(array('label' => Lang::get('module::app.name'), 'index' => 'name' ,'name' => 'module_app_name'))
	    	->renderGrid();
			!!}
		</div>
	</div>
</div>
<div id='rh-em-journals-section' class="row collapse in section-block">
	{!! Form::journals('rh-em-', $appInfo['id']) !!}
</div>
<div id='rh-em-form-section' class="row collapse">
	<div class="col-lg-12 col-md-12">
		<div class="form-container">
			{!! Form::open(array('id' => 'rh-em-form', 'url' => URL::to('recurso-humano/mantenimiento/empleados'), 'role'  =>  'form', 'onsubmit' => 'return false;')) !!}
				<legend id="rh-em-form-new-title" class="hidden">{{ Lang::get('module::app.formNewTitle') }}</legend>
				<legend id="rh-em-form-edit-title" class="hidden">{{ Lang::get('module::app.formEditTitle') }}</legend>
				<div class="row">
					<div class="col-lg-6 col-md-6">
						<div class="form-group mg-hm">
							{!! Form::label('rh-em-nombre', Lang::get('decima-module::empleado-management.nombre'), array('class' => 'control-label')) !!}
					    {!! Form::text('rh-em-nombre', null , array('id' => 'rh-em-nombre', 'class' => 'form-control', 'data-mg-required' => '')) !!}
					    {!! Form::hidden('rh-em-id', null, array('id' => 'rh-em-id')) !!}
			  		</div>
						<div class="form-group mg-hm">
							{!! Form::label('rh-em-apellido', Lang::get('decima-module::empleado-management.apellido'), array('class' => 'control-label')) !!}
								<div class="input-group">
								<span class="input-group-addon">
									<i class="fa fa-car"></i>
								</span>
								{!! Form::text('rh-em-apellido', null , array('id' => 'rh-em-apellido', 'class' => 'form-control', 'data-mg-required' => '')) !!}
							</div>
						</div>
						<div class="form-group mg-hm">
							{!! Form::label('rh-em-descripcion', Lang::get('decima-module::empleado-management.descripcion'), array('class' => 'control-label')) !!}
							{!! Form::textareacustom('acct-jm-remark', 2, 500, array('class' => 'form-control', 'data-mg-required' => '')) !!}
						</div>
					</div>
					<div class="col-lg-6 col-md-6">
						<div class="form-group mg-hm">
							{!! Form::label('rh-em-salario', Lang::get('decima-module::empleado-management.salario'), array('class' => 'control-label')) !!}
							{!! Form::money('rh-em-salario', array('class' => 'form-control', 'data-mg-required' => '', 'defaultvalue' => Lang::get('form.defaultNumericValue')), Lang::get('form.defaultNumericValue')) !!}
						</div>
						<div class="form-group mg-hm">
							{!! Form::label('rh-em-edad', Lang::get('decima-module::empleado-management.edad'), array('class' => 'control-label')) !!}
							{!! Form::text('rh-em-edad', null , array('id' => 'rh-em-edad', 'class' => 'form-control', 'data-mg-required' => '', 'data-mg-validator'=>'positiveInteger' )) !!}
						</div>
						<div class="form-group mg-hm">
							{!! Form::label('rh-em-puesto', Lang::get('decima-module::empleado-management.puesto'), array('class' => 'control-label')) !!}
							{!! Form::autocomplete('rh-em-puesto', array(), array('class' => 'form-control', 'data-mg-required' => ''), 'rh-em-puesto', 'rh-em-puesto-id', null, 'fa-files-o') !!}
							{!! Form::hidden('rh-em-puesto-id', null, array('id'  =>  'rh-em-puesto-id')) !!}
						</div>
					</div>
				</div>
			{!! Form::close() !!}
		</div>
	</div>
</div>
<div id='rh-em-modal-delete' class="modal fade" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
  <div class="modal-dialog modal-sm rh-em-btn-delete">
    <div class="modal-content">
			<div class="modal-body" style="padding: 20px 20px 0px 20px;">
				<p id="rh-em-delete-message" data-default-label="{{ Lang::get('module::app.deleteMessageConfirmation') }}"></p>
      </div>
			<div class="modal-footer" style="text-align:center;">
				<button type="button" class="btn btn-default" data-dismiss="modal">{{ Lang::get('form.no') }}</button>
				<button id="rh-em-btn-modal-delete" type="button" class="btn btn-primary">{{ Lang::get('form.yes') }}</button>
			</div>
    </div>
  </div>
</div>
@parent
@stop
