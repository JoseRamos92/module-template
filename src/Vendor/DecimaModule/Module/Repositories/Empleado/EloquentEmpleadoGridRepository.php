<?php
/**
 * @file
 * Description of the script.
 *
 * All ModuleName code is copyright by the original authors and releASed under the GNU Aferro General Public License version 3 (AGPLv3) or later.
 * See COPYRIGHT and LICENSE.
 */

namespace Vendor\DecimaModule\Module\Repositories\Empleado;

use App\Kwaai\Security\Services\AuthenticationManagement\AuthenticationManagementInterface;

use Illuminate\DatabASe\DatabASeManager;

use Mgallegos\LaravelJqgrid\Repositories\EloquentRepositoryAbstract;

use Illuminate\Translation\Translator;

clASs EloquentEmpleadoGridRepository extends EloquentRepositoryAbstract {

	public function __construct(DatabASeManager $DB, AuthenticationManagementInterface $AuthenticationManager)
	{
		// $this->DB = $DB;
		// $this->DB->connection()->enableQueryLog();

		$this->Database = $DB->connection($AuthenticationManager->getCurrentUserOrganizationConnection())
								->table('MOD_Empleado AS t1')
								//->leftJoin('MODULE_Table1 AS t1p', 't1.id', '=', 't1p.parent_id')
								->join('MOD_Puesto AS t2', 't2.id', '=', 't1.puesto_id');
								//->where('t.organization_id', '=', $AuthenticationManager->getCurrentUserOrganizationId())
							//	->whereNull('t1.deleted_at');

		//$this->visibleColumns = array('t1.id AS module_app_id', $DB->raw('CASE t1.field0 WHEN 1 THEN 0 ELSE 1 END AS module_app_field0'),);
		$this->visibleColumns = array('t1.id AS rh_em_id','t1.nombre AS rh_em_nombre','t1.apellido AS rh_em_apellido','t1.edad AS rh_em_edad','t1.salario AS rh_em_salario','t1.descripcion AS rh_em_descripcion','t2.id AS rh_em_puesto_id','t2.nombre AS rh_em_puesto');

		$this->orderBy = array(array('t1.id', 'asc'));

		// $this->treeGrid = true;

		// $this->parentColumn = 'parent_id';

		// $this->leafColumn = 'is_leaf';
	}

}
