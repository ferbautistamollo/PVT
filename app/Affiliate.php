<?php

namespace App;
use Carbon;

use Illuminate\Database\Eloquent\Model;
use Laratrust\Traits\LaratrustUserTrait;
use Illuminate\Support\Facades\Storage;

class Affiliate extends Model
{
    use Traits\EloquentGetTableNameTrait;
    use Traits\RelationshipsTrait;

    public $relationships = ['City', 'AffiliateState'];
    // protected $appends = ['picture_saved', 'fingerprint_saved', 'full_name'];
    // protected $hidden = ['pivot'];
    protected $fillable = [
        'user_id',
        'affiliate_state_id',
        'city_identity_card_id',
        'city_birth_id',
        'degree_id',
        'unit_id',
        'category_id',
        'pension_entity_id',
        'identity_card',
        'registration',
        'type',
        'last_name',
        'mothers_last_name',
        'first_name',
        'second_name',
        'surname_husband',
        'civil_status',
        'gender',
        'birth_date',
        'date_entry',
        'date_death',
        'reason_death',
        'date_derelict',
        'reason_derelict',
        'change_date',
        'phone_number',
        'cell_phone_number',
        'afp',
        'nua',
        'item',
        'is_duedate_undefined',
        'due_date'
      ];

    public function getPictureSavedAttribute()
    {
        $base_path = 'picture/';
        return Storage::disk('ftp')->exists($base_path . $this->id . '_perfil.jpg');
    }

    public function getFingerprintSavedAttribute()
    {
        $base_path = 'picture/';
        $fingerprint_pictures = ['_left_four.png', '_right_four.png', '_thumbs.png'];
        $fingerprint_exists = false;
        foreach ($fingerprint_pictures as $picture) {
            $fingerprint_exists |= Storage::disk('ftp')->exists($base_path . $this->id . $picture);
        }
        return boolval($fingerprint_exists);
    }
    public function getFullNameAttribute()
    {
      return preg_replace('/[[:blank:]]+/', ' ', join(' ', [$this->first_name, $this->second_name, $this->last_name, $this->mothers_last_name]));
    }

    public function category()
    {
      return $this->belongsTo(Category::class);
    }
    public function degree()
    {
      return $this->belongsTo(Degree::class);
    }
    public function unit()
    {
      return $this->belongsTo(Unit::class);
    }
    public function city_identity_card()
    {
      return $this->belongsTo(City::class,'city_identity_card_id', 'id');
    }
    public function affiliate_state()
    {
      return $this->belongsTo(AffiliateState::class);
    }
    public function city_birth()
    {
      return $this->belongsTo(City::class, 'city_birth_id', 'id');
    }
    public function pension_entity()
    {
      return $this->belongsTo(PensionEntity::class);
    }
      // add records
    public function records()
    {
      return $this->morphMany(Record::class, 'recordable');
    }
      //address
    public function addresses()
    {
      return $this->morphToMany(Address::class, 'addressable')->withTimestamps();
    }
    //spouses
    public function spouse()
    {
      return $this->hasMany(Spouse::class);
    }
    //contributions
    public function contribution()
    {
      return $this->hasMany(Contribution::class);
    }
    public function findContributions($affiliate_id, $boolean)
    {
      $boletas=array();
      if($boolean==true)
      {
        $contribution = Contribution::where('affiliate_id', '=', $affiliate_id)->get()->last();
        return $contribution;
      }
      else{
        $contribution= Contribution::where('affiliate_id', '=', $affiliate_id)->orderBy('month_year', 'desc')->get()->toArray();
        $boletas[0]=$contribution[0];
        $boletas[1]=$contribution[1];
        $boletas[2]=$contribution[2];
        return $boletas;
      }
    }

    public function guarantees()
    {
        return $this->belongsToMany(Loan::class, 'loan_affiliates')->withPivot(['payment_porcentage'])->whereGuarantor(true)->orderBy('loans.created_at', 'desc');
    }

    public function loans()
    {
        return $this->belongsToMany(Loan::class, 'loan_affiliates')->withPivot(['payment_porcentage'])->whereGuarantor(false)->orderBy('loans.created_at', 'desc');
    }

    public function active_loans()
    {
        return $this->verify_balance($this->loans);
    }
    public function active_guarantees()
    {
        return $this->verify_balance($this->guarantees);
    }

    private function verify_balance($loans)
    {
        $active_loans = [];
        foreach ($loans as $loan) {
            $loan->balance = $loan->balance;
            if ($loan->balance > 0) {
                $loan->estimated_quota = $loan->estimated_quota;
                array_push($active_loans, $loan);
            }
        }
        return $active_loans;
    }

    //document
    public function submitted_documents()
    {
        return $this->hasMany(AffiliateSubmittedDocument::class);
    }

    public function disbursements()
    {
        return $this->morphMany(Loan::class, 'disbursable');
    }
    // verify if a loan is debt
    public function debt_payment($loan_id){
      $payments=(Loan::find($loan_id))->payments;
      foreach($payments as $pay){ 
          if(($pay->penal_payment)>0){ $debt=true; }else{ $debt=false; }
          if($debt){ break;}  
      }
      return $debt;
    } 
    // verify if an affiliate is cpop
    public function verify_cpop($id){
      $affiliate=Affiliate::find($id);$debt_loans=[];$c=1;
      if($affiliate){
          $loans_affiliate=$affiliate->loans->sortByDesc('disbursement_date');
          if(count($loans_affiliate)>0){
            foreach($loans_affiliate as $loans_affi){ 
              if($loans_affi->state->name = "liquidado"){
                $loan_payments_debt= $this->debt_payment($loans_affi->id);
                if($loan_payments_debt){
                  $debt_loans[$c] = $loans_affi;
                  $c++;
                }
              }
            }              
            if($debt_loans!=[]){
              $cpop=reset($debt_loans);
            }else{
              $cpop=true;
            }
             
          }else {
            $cpop=$debt_loans;
          }
           
      }else{
        $cpop=$debt_loans;
      }
      return $cpop;  
    } 
   
}