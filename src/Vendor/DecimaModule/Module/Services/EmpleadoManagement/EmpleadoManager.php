<?php
/**
 * @file
 * Module App Management Interface Implementation.
 *
 * All ModuleName code is copyright by the original authors and released under the GNU Aferro General Public License version 3 (AGPLv3) or later.
 * See COPYRIGHT and LICENSE.
 */

namespace Vendor\DecimaModule\Module\Services\EmpleadoManagement;

use App\Kwaai\Security\Services\AuthenticationManagement\AuthenticationManagementInterface;

use App\Kwaai\Security\Services\JournalManagement\JournalManagementInterface;

use App\Kwaai\Security\Repositories\Journal\JournalInterface;

use Mgallegos\LaravelJqgrid\Encoders\RequestedDataInterface;

use Vendor\DecimaModule\Module\Repositories\Empleado\EloquentEmpleadoGridRepository;

use Vendor\DecimaModule\Module\Repositories\Empleado\EmpleadoInterface;
use Carbon\Carbon;

use Illuminate\Config\Repository;

use Illuminate\Translation\Translator;

use Illuminate\Database\DatabaseManager;


class EmpleadoManager implements EmpleadoManagementInterface {

  /**
   * Authentication Management Interface
   *
   * @var App\Kwaai\Security\Services\AuthenticationManagement\AuthenticationManagementInterface
   *
   */
  protected $AuthenticationManager;

  /**
  * Journal Management Interface (Security)
  *
  * @var App\Kwaai\Security\Services\JournalManagement\JournalManagementInterface
  *
  */
  protected $JournalManager;

  /**
  * Journal (Security)
  *
  * @var App\Kwaai\Security\Repositories\Journal\JournalInterface
  *
  */
  protected $Journal;

  /**
	 * Grid Encoder
	 *
	 * @var Mgallegos\LaravelJqgrid\Encoders\RequestedDataInterface
	 *
	 */
	protected $GridEncoder;

  /**
	 * Eloquent Grid Repository
	 *
	 * @var App\Kwaai\Template\Repositories\Empleado\EloquentEmpleadoGridRepository
	 *
	 */
	protected $EloquentEmpleadoGridRepository;

  /**
	 *  Module Table Name Interface
	 *
	 * @var App\Kwaai\Template\Repositories\Empleado\EmpleadoInterface
	 *
	 */
	protected $Empleado;

  /**
   * Carbon instance
   *
   * @var Carbon\Carbon
   *
   */
  protected $Carbon;

  /**
   * Laravel Database Manager
   *
   * @var Illuminate\Database\DatabaseManager
   *
   */
  protected $DB;

  /**
   * Laravel Translator instance
   *
   * @var Illuminate\Translation\Translator
   *
   */
  protected $Lang;

  /**
   * Laravel Repository instance
   *
   * @var Illuminate\Config\Repository
   *
   */
  protected $Config;

	public function __construct(AuthenticationManagementInterface $AuthenticationManager, JournalManagementInterface $JournalManager, JournalInterface $Journal, RequestedDataInterface $GridEncoder, EloquentEmpleadoGridRepository $EloquentEmpleadoGridRepository, EmpleadoInterface $Empleado, Carbon $Carbon, DatabaseManager $DB, Translator $Lang, Repository $Config)
	{
    $this->AuthenticationManager = $AuthenticationManager;

    $this->JournalManager = $JournalManager;

    $this->Journal = $Journal;

    $this->GridEncoder = $GridEncoder;

    $this->EloquentEmpleadoGridRepository = $EloquentEmpleadoGridRepository;

    $this->Empleado = $Empleado;

    $this->Carbon = $Carbon;

    $this->DB = $DB;

		$this->Lang = $Lang;

		$this->Config = $Config;
	}

  /**
   * Echo grid data in a jqGrid compatible format
   *
   * @param array $post
   *	All jqGrid posted data
   *
   * @return void
   */
  public function getGridData(array $post)
  {
    $this->GridEncoder->encodeRequestedData($this->EloquentEmpleadoGridRepository, $post);
  }

  /**
	 * Create a new ...
	 *
	 * @param array $input
   * 	An array as follows: array('field0'=>$field0, 'field1'=>$field1
   *                            );
   *
	 * @return JSON encoded string
	 *  A string as follows:
	 *	In case of success: {"success" : form.defaultSuccessSaveMessage}
	 */
	public function create(array $input)
	{
    unset($input['_token'],$input['puesto']);

    $loggedUserId = $this->AuthenticationManager->getLoggedUserId();//linea del id del usuario conectado
    $organizationId = $this->AuthenticationManager->getCurrentUserOrganizationId();//

    $input = eloquent_array_filter_for_insert($input);
		//$input = array_add($input, 'organization_id', $organizationId);
    // $input['date'] = $this->Carbon->createFromFormat($this->Lang->get('form.phpShortDateFormat'), $input['date'])->format('Y-m-d');


    //transaccion, variables q estan dentro de otroa funcion
    $this->DB->transaction(function() use ($input, $loggedUserId, $organizationId)
		{//Empleado es el repositorio
      $Empleado = $this->Empleado->create($input);
      //tabla de auditoria
      $Journal = $this->Journal->create(array('journalized_id' => $Empleado->id, 'journalized_type' => $this->Empleado->getTable(), 'user_id' => $loggedUserId, 'organization_id' => $organizationId));
      //mensaje de auditoria se ha agregado el empleado
      $this->Journal->attachDetail($Journal->id, array('note' => $this->Lang->get('decima-module::Empleado-management.addedJournal', array('Empleado' => $Empleado->nombre . ' ' . $Empleado->apellido)), $Journal));

    });
    //json_encode transforma en json el array
    return json_encode(array('success' => $this->Lang->get('form.defaultSuccessSaveMessage')));
  }

  /**
   * Update an existing ...
   *
   * @param array $input
   * 	An array as follows: array('id' => $id, 'field0'=>$field0, 'field1'=>$field1
   *
   * @return JSON encoded string
   *  A string as follows:
   *	In case of success: {"success" : form.defaultSuccessUpdateMessage}
   */
  public function update(array $input)
  {
    unset($input['_token'],$input['puesto']);
    $input = eloquent_array_filter_for_update($input);
    // $input['date'] = $this->Carbon->createFromFormat($this->Lang->get('form.phpShortDateFormat'), $input['date'])->format('Y-m-d');

    $this->DB->transaction(function() use (&$input)
    {
      $Empleado = $this->Empleado->byId($input['id']);
      $unchangedEmpleadoValues = $Empleado->toArray();

      $this->Empleado->update($input, $Empleado);

      $diff = 0;

      foreach ($input as $key => $value)
      {
        if($unchangedEmpleadoValues[$key] != $value)
        {
          $diff++;

          if($diff == 1)
          {
            $Journal = $this->Journal->create(array('journalized_id' => $Empleado->id, 'journalized_type' => $this->Empleado->getTable(), 'user_id' => $this->AuthenticationManager->getLoggedUserId(), 'organization_id' => $this->AuthenticationManager->getCurrentUserOrganizationId()));
          }

          if($key == 'field0')
          {
            $this->Journal->attachDetail($Journal->id, array('field' => $this->Lang->get('module::app.field0'), 'field_lang_key' => 'module::app.field0', 'old_value' => $this->Lang->get('module::app.' . $unchangedEmpleadoValues[$key]), 'new_value' => $this->Lang->get('module::app.' . $value)), $Journal);
          }
          else if ($key == 'field1')
          {
            $this->Journal->attachDetail($Journal->id, array('field' => $this->Lang->get('module::app.field1'), 'field_lang_key' => 'module::app.field1', 'old_value' => ' ', 'new_value' => ''), $Journal);
          }
          else
          {
            $this->Journal->attachDetail($Journal->id, array('field' => $this->Lang->get('module::app.' . camel_case($key)), 'field_lang_key' => 'module::app.' . camel_case($key), 'old_value' => $unchangedEmpleadoValues[$key], 'new_value' => $value), $Journal);
          }
        }
      }
    });

    return json_encode(array('success' => $this->Lang->get('form.defaultSuccessUpdateMessage')));
  }

  /**
   * Delete an existing ... (soft delete)
   *
   * @param array $input
	 * 	An array as follows: array(id => $id);
   *
   * @return JSON encoded string
   *  A string as follows:
   *	In case of success: {"success" : form.defaultSuccessDeleteMessage}
   */
  public function delete0(array $input)
  {
    $this->DB->transaction(function() use ($input)
    {
      $loggedUserId = $this->AuthenticationManager->getLoggedUserId();
      $organizationId = $this->AuthenticationManager->getCurrentUserOrganization('id');

      $Empleado = $this->Empleado->byId($input['id']);
      $Journal = $this->Journal->create(array('journalized_id' => $input['id'], 'journalized_type' => $this->Empleado->getTable(), 'user_id' => $loggedUserId, 'organization_id' => $organizationId));
      $this->Journal->attachDetail($Journal->id, array('note' => $this->Lang->get('module::app.deletedJournal', array('number' => $Empleado->number)), $Journal));
      // $this->Empleado->delete(array($input['id']));
    });

    return json_encode(array('success' => $this->Lang->get('module::app.successDeletedMessage')));
  }

  /**
   * Delete existing ... (soft delete)
   *
   * @param array $input
	 * 	An array as follows: array($id0, $id1,…);
   *
   * @return JSON encoded string
   *  A string as follows:
   *	In case of success: {"success" : form.defaultSuccessDeleteMessage}
   */
   public function delete1(array $input)
   {
     $count = 0;

     $this->DB->transaction(function() use ($input)
     {
       $loggedUserId = $this->AuthenticationManager->getLoggedUserId();
       $organizationId = $this->AuthenticationManager->getCurrentUserOrganization('id');

       foreach ($input['id'] as $key => $id)
       {
         $count++;

         $Journal = $this->Journal->create(array('journalized_id' => $id, 'journalized_type' => $this->Empleado->getTable(), 'user_id' => $loggedUserId, 'organization_id' => $organizationId));
         $this->Journal->attachDetail($Journal->id, array('note' => $this->Lang->get('module::app.deletedJournal', array('email' => $Empleado->email, 'organization' => $organizationName))), $Journal);

         $this->Empleado->delete(array($id));
       }

       if($count == 1)
       {
         return json_encode(array('success' => $this->Lang->get('module::app.successDeleted0Message')));
       }
       else
       {
         return json_encode(array('success' => $this->Lang->get('module::app.successDeleted1Message')));
       }

     });
   }
}
