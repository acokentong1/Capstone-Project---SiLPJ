<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::table('belanjas', function(Blueprint $table){
   if(!Schema::hasColumn('belanjas','school_profile_id')){
    $table->foreignId('school_profile_id')->nullable()->after('user_id');
   }
  });
 }
 public function down(): void {
  Schema::table('belanjas', function(Blueprint $table){
   if(Schema::hasColumn('belanjas','school_profile_id')){
    $table->dropColumn('school_profile_id');
   }
  });
 }
};
