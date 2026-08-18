<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentCertificate extends Model {
    protected $fillable = ['school_id','student_id','issued_by','certificate_template_id','title','course_name','description','certificate_number','verification_code','issued_at','pdf_path','status','metadata'];
    protected function casts():array{return['issued_at'=>'datetime','metadata'=>'array'];}
    public function student():BelongsTo{return $this->belongsTo(User::class,'student_id');}
    public function issuer():BelongsTo{return $this->belongsTo(User::class,'issued_by');}
    public function template():BelongsTo{return $this->belongsTo(CertificateTemplate::class,'certificate_template_id');}
    public static function generateNumber():string{return'CERT-'.strtoupper(uniqid()).'-'.rand(100,999);}
    public static function generateVerificationCode():string{return strtoupper(bin2hex(random_bytes(8)));}
}