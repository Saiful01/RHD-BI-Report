<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tender extends Model
{
    use HasFactory;


    protected $fillable = [
        'tenderid',
        'departmentid',
        'ministry_division',
        'agency',
        'procuring_entity_name',
        'procuring_entity_code',
        'procuring_entity_district',
        'contract_award_for',
        'proposal_reference_no',
        'procurement_method',
        'budget_source_funds',
        'development_partner',
        'project_name',
        'tender_package_no',
        'tender_package_name',
        'date_advertisement',
        'date_notification_award',
        'date_contract_signing',
        'proposed_date_contract_start',
        'proposed_date_contract_completion',
        'no_tenders_sold',
        'no_tenders_received',
        'tenders_responsive',
        'description_contract',
        'contract_value',
        'contract_value_usd',
        'supplier_name',
        'supplier_location',
        'delivery_location',
        'performance_security_provided_due_time',
        'contract_singed_due_time',
        'authorised_officer',
        'designation_authorised_officer',
        'is_exist_cms'
    ];


    protected $casts = [
        'date_advertisement' => 'datetime',
        'date_notification_award' => 'date',
        'date_contract_signing' => 'date',
        'proposed_date_contract_start' => 'date',
        'proposed_date_contract_completion' => 'date',
        'contract_value' => 'decimal:4',
        'contract_value_usd' => 'decimal:6',
        'no_tenders_sold' => 'integer',
        'no_tenders_received' => 'integer',
        'tenders_responsive' => 'integer',
        'is_exist_cms' => 'integer',
    ];

    public function items()
    {
        return $this->hasMany(TenderItem::class);
    }
}
