<?php
/**
 * @file
 * Description of the script.
 *
 * All ModuleName code is copyright by the original authors and released under the GNU Aferro General Public License version 3 (AGPLv3) or later.
 * See COPYRIGHT and LICENSE.
 */

namespace Vendor\DecimaModule\Module\Repositories\EmpleadoManagerService;

use Illuminate\Database\Eloquent\Model;

use Vendor\DecimaModule\Module\EmpleadoManagerService;

class EloquentEmpleadoManagerService implements EmpleadoManagerServiceInterface {

  /**
   * Empleado
   *
   * @var App\Kwaai\Template\Empleado;
   *
   */
  protected $Empleado;

  /**
   * Database Connection
   *
   * @var string
   *
   */
  protected $databaseConnectionName;

  public function __construct(Model $Empleado, $databaseConnectionName)
  {
      $this->Empleado = $Empleado;

      $this->databaseConnectionName = $databaseConnectionName;

      $this->Empleado->setConnection($databaseConnectionName);
  }

  /**
   * Get table name
   *
   * @return string
   */
  public function getTable()
  {
    return $this->Empleado->getTable();
  }

  /**
   * Get a ... by ID
   *
   * @param  int $id
   *
   * @return Mgallegos\DecimaEmpleadoing\Empleado
   */
  public function byId($id)
  {
  	return $this->Empleado->on($this->databaseConnectionName)->find($id);
  }

  /**
   * Retrieve ... by organization
   *
   * @param  int $id Organization id
   *
   * @return Illuminate\Database\Eloquent\Collection
   */
  public function byOrganization($id)
  {
    return $this->Empleado->where('organization_id', '=', $id)->get();
  }

  /**
   * Create a new ...
   *
   * @param array $data
   * 	An array as follows: array('field0'=>$field0, 'field1'=>$field1
   *                            );
   *
   * @return boolean
   */
  public function create(array $data)
  {
    $Empleado = new Empleado();
    $Empleado->setConnection($this->databaseConnectionName);
    $Empleado->fill($data)->save();

    return $Empleado;
  }

  /**
   * Update an existing ...
   *
   * @param array $data
   * 	An array as follows: array('field0'=>$field0, 'field1'=>$field1
   *                            );
   *
   * @param Mgallegos\DecimaEmpleadoing\Empleado $Empleado
   *
   * @return boolean
   */
  public function update(array $data, $Empleado = null)
  {
    if(empty($Empleado))
    {
      $Empleado = $this->byId($data['id']);
    }

    foreach ($data as $key => $value)
    {
      $Empleado->$key = $value;
    }

    return $Empleado->save();
  }

}
