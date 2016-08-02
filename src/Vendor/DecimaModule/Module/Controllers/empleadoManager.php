<?php
/**
 * @file
 * Account Manager Controller.
 *
 * All DecimaAccounting code is copyright by the original authors and released under the GNU Aferro General Public License version 3 (AGPLv3) or later.
 * See COPYRIGHT and LICENSE.
 */

namespace Vendor\DecimaModule\Module\Controllers;

use Illuminate\Session\SessionManager;

use Illuminate\Http\Request;

use Vendor\DecimaModule\Module\Services\EmpleadoManagement\EmpleadoManagementInterface;

use Illuminate\View\Factory;

use App\Http\Controllers\Controller;

class empleadoManager extends Controller {

	/**
	 * Account Manager Service
	 *
	 * @var Mgallegos\DecimaAccounting\Accounting\Services\AccountManagement\AccountManagementInterface
	 *
	 */
	protected $EmpleadoManagerService;

	/**
	 * View
	 *
	 * @var Illuminate\View\Factory
	 *
	 */
	protected $View;

	/**
	 * Input
	 *
	 * @var Illuminate\Http\Request
	 *
	 */
	protected $Input;

	/**
	 * Session
	 *
	 * @var Illuminate\Session\SessionManager
	 *
	 */
	protected $Session;

	public function __construct(EmpleadoManagementInterface $EmpleadoManagerService, Factory $View, Request $Input, SessionManager $Session)
	{
		 $this->EmpleadoManagerService = $EmpleadoManagerService;

		$this->View = $View;

		$this->Input = $Input;

		$this->Session = $Session;
	}

	public function getIndex()
	{
		return $this->View->make('decima-module::empleado-management')//nombre de la vista empleado-management, decima-module
						->with('newempleadoAction', $this->Session->get('newempleadoAction', false))
						->with('editempleadoAction', $this->Session->get('editempleadoAction', false))
						->with('deleteempleadoAction', $this->Session->get('deleteempleadoAction', false));
					//	->with('accounts', $this->EmpleadoManagerService->getGroupsAccounts())

	}

	public function postGridData()
	{
		return $this->EmpleadoManagerService->getGridData( $this->Input->all() );
	}

	public function postCreate()
	{
		return $this->EmpleadoManagerService->create( $this->Input->json()->all() );
	}

	public function postUpdate()
	{
		return $this->EmpleadoManagerService->update( $this->Input->json()->all() );
	}

	public function postDelete()
	{
		return $this->EmpleadoManagerService->delete( $this->Input->json()->all() );
	}

	// public function postAccountChildren()
	// {
	// 	return $this->EmpleadoManagerService->getAccountChildren( $this->Input->json()->all() );
	// }
	//
	// public function postAccountChildrenIds()
	// {
	// 	return $this->EmpleadoManagerService->getAccountChildrenIdsJson( $this->Input->json()->all());
	// }


}
