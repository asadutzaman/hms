<?php

namespace App\Validators;

/**
 * Dispense records are only ever created via
 * PrescriptionDispenseController::dispense() (validated inside
 * PrescriptionDispenseService), not through the standard store/update
 * routes, so no field rules are needed here.
 */
class PrescriptionDispenseValidator extends BaseValidator
{
    protected $request;

    public function __construct()
    {
        $this->request = request();
    }

    public function rules()
    {
        return [];
    }

}
