<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tenders', function (Blueprint $table) {
            $table->id();
            $table->string('tenderid', 15)->nullable()->comment('Tender/Proposal ID');
            $table->integer('departmentid')->nullable();
            $table->string('ministry_division', 230)->nullable()->comment('Ministry/Division');
            $table->string('agency', 230)->nullable()->comment('Agency');
            $table->string('procuring_entity_name', 230)->nullable()->comment('Procuring Entity Name');
            $table->string('procuring_entity_code', 230)->nullable()->comment('Procuring Entity Code');
            $table->string('procuring_entity_district', 230)->nullable()->comment('Procuring Entity District');
            $table->string('contract_award_for', 230)->nullable()->comment('Contract Award for');
            $table->string('proposal_reference_no', 230)->nullable()->comment('Invitation/Proposal Reference No.');
            $table->string('procurement_method', 100)->nullable()->comment('Procurement Method');
            $table->string('budget_source_funds', 230)->nullable()->comment('Budget and Source of Funds');
            $table->string('development_partner', 230)->nullable()->comment('Development Partner (if applicable)');
            $table->string('project_name', 230)->nullable()->comment('Project/Programme Name (if applicable)');
            $table->string('tender_package_no', 230)->nullable()->comment('Tender/Proposal Package No.');
            $table->string('tender_package_name', 230)->nullable()->comment('Tender/Proposal Package Name');

            // Dates and Timestamps
            $table->timestamp('date_advertisement')->nullable()->comment('Date of Advertisement');
            $table->date('date_notification_award')->nullable()->comment('Date of Notification of Award');
            $table->date('date_contract_signing')->nullable()->comment('Date of Contract Signing');
            $table->date('proposed_date_contract_start')->nullable()->comment('Proposed Date of Contract Start');
            $table->date('proposed_date_contract_completion')->nullable()->comment('Proposed Date of Contract Completion');

            // Statistics
            $table->integer('no_tenders_sold')->default(0)->comment('No. of Tenders/Proposals Sold');
            $table->integer('no_tenders_received')->default(0)->comment('No. of Tenders/Proposals Received');
            $table->integer('tenders_responsive')->default(0);

            // Contract Details
            $table->string('description_contract', 230)->nullable()->comment('Brief Description of Contract');
            $table->decimal('contract_value', 20, 4)->default(0.0000)->comment('Contract Value (Taka)');
            $table->decimal('contract_value_usd', 20, 6)->nullable()->comment('Contract Value (USD)');
            $table->string('supplier_name', 230)->nullable()->comment('Name of Supplier/Contractor/Consultant');
            $table->string('supplier_location', 230)->nullable()->comment('Location of Supplier/Contractor/Consultant');
            $table->string('delivery_location', 230)->nullable()->comment('Location of Delivery/Works/Consultancy');

            // Compliance and Officers
            $table->string('performance_security_provided_due_time', 100)->nullable()->comment('Was Performance Security provided in due time?');
            $table->string('contract_singed_due_time', 100)->nullable()->comment('Was the Contract Signed in due time?');
            $table->string('authorised_officer', 150)->nullable()->comment('Name of Authorised Officer');
            $table->string('designation_authorised_officer', 150)->nullable()->comment('Designation of Authorised Officer');

            $table->integer('is_exist_cms')->default(0)->comment('1 = Yes, 0 = No');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenders');
    }
};
