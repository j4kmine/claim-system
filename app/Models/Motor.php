<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use JamesDordoy\LaravelVueDatatable\Traits\LaravelVueDatatableTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Auth;

class Motor extends Model
{
    use LaravelVueDatatableTrait, HasFactory;

    protected $appends = ['format_start_date', 'format_expiry_date', 'format_price', 'format_valid_till', 'format_submitted_at'];

    const NRIC_TYPES = [
        '',
        'NRIC',
        'FIN',
        'Passport',
        'Birth Certificate',
        'Others'
    ]; 

    const RESIDENTIALS = [
        '',
        'Singapore',
        'PR',
        'Foreigner'
    ];

    const NATIONALITIES = ["SG" => 'Singapore', "AF" => 'Afghanistan', "AL" => 'Albania', "DZ" => 'Algeria', "AS" => 'American Samoa', "AD" => 'Andorra', "AO" => 'Angola', "AI" => 'Anguilla', "AQ" => 'Antarctica', "AG" => 'Antigua and Barbuda', "AR" => 'Argentina', "AM" => 'Armenia', "AW" => 'Aruba', "AU" => 'Australia', "AT" => 'Austria', "AZ" => 'Azerbaijan', "BS" => 'Bahamas', "BH" => 'Bahrain', "BD" => 'Bangladesh', "BB" => 'Barbados', "BY" => 'Belarus', "BE" => 'Belgium', "BZ" => 'Belize', "BJ" => 'Benin', "BM" => 'Bermuda', "BT" => 'Bhutan', "BO" => 'Bolivia', "BA" => 'Bosnia and Herzegovina', "BW" => 'Botswana', "BR" => 'Brazil', "IO" => 'British Indian Ocean Territory', "VG" => 'British Virgin Islands', "BN" => 'Brunei', "BG" => 'Bulgaria', "BF" => 'Burkina Faso', "BI" => 'Burundi', "KT" => 'COTEDIVOIRE', "KH" => 'Cambodia', "CM" => 'Cameroon', "CA" => 'Canada', "CV" => 'Cape Verde', "KY" => 'Cayman Islands', "CF" => 'Central African Republic', "TD" => 'Chad', "CL" => 'Chile', "CN" => 'China', "CX" => 'Christmas Island', "CC" => 'Cocos Islands', "CO" => 'Colombia', "KM" => 'Comoros', "CK" => 'Cook Islands', "CR" => 'Costa Rica', "HR" => 'Croatia', "CU" => 'Cuba', "CW" => 'Curacao', "CY" => 'Cyprus', "CZ" => 'Czech Republic', "DD" => 'DDGK', "CD" => 'Democratic Republic of the Congo', "DK" => 'Denmark', "DJ" => 'Djibouti', "DM" => 'Dominica', "DO" => 'Dominican Republic', "TL" => 'East Timor', "EC" => 'Ecuador', "EG" => 'Egypt', "SV" => 'El Salvador', "GQ" => 'Equatorial Guinea', "ER" => 'Eritrea', "EE" => 'Estonia', "ET" => 'Ethiopia', "FK" => 'Falkland Islands', "FO" => 'Faroe Islands', "FJ" => 'Fiji', "FI" => 'Finland', "FR" => 'France', "PF" => 'French Polynesia', "GA" => 'Gabon', "GM" => 'Gambia', "GE" => 'Georgia', "DE" => 'Germany', "GH" => 'Ghana', "GI" => 'Gibraltar', "GR" => 'Greece', "GL" => 'Greenland', "GD" => 'Grenada', "GU" => 'Guam', "GT" => 'Guatemala', "GG" => 'Guernsey', "GN" => 'Guinea', "GW" => 'Guinea-Bissau', "GY" => 'Guyana', "HT" => 'Haiti', "HN" => 'Honduras', "HK" => 'Hong Kong', "HU" => 'Hungary', "IS" => 'Iceland', "IN" => 'India', "ID" => 'Indonesia', "IR" => 'Iran', "IQ" => 'Iraq', "IE" => 'Ireland', "IM" => 'Isle of Man', "IL" => 'Israel', "IT" => 'Italy', "CI" => 'Ivory Coast', "JM" => 'Jamaica', "JP" => 'Japan', "JE" => 'Jersey', "JO" => 'Jordan', "KZ" => 'Kazakhstan', "KE" => 'Kenya', "KI" => 'Kiribati', "XK" => 'Kosovo', "KW" => 'Kuwait', "KG" => 'Kyrgyzstan', "LA" => 'Laos', "LV" => 'Latvia', "LB" => 'Lebanon', "LS" => 'Lesotho', "LR" => 'Liberia', "LY" => 'Libya', "LI" => 'Liechtenstein', "LT" => 'Lithuania', "LU" => 'Luxembourg', "MO" => 'Macao', "MK" => 'Macedonia', "MG" => 'Madagascar', "MW" => 'Malawi', "MY" => 'Malaysia', "MV" => 'Maldives', "ML" => 'Mali', "MT" => 'Malta', "MH" => 'Marshall Islands', "MR" => 'Mauritania', "MU" => 'Mauritius', "YT" => 'Mayotte', "MX" => 'Mexico', "FM" => 'Micronesia', "MD" => 'Moldova', "MC" => 'Monaco', "MN" => 'Mongolia', "ME" => 'Montenegro', "MS" => 'Montserrat', "MA" => 'Morocco', "MZ" => 'Mozambique', "MM" => 'Myanmar', "NA" => 'Namibia', "NR" => 'Nauru', "NP" => 'Nepal', "NL" => 'Netherlands', "AN" => 'Netherlands Antilles', "NC" => 'New Caledonia', "NZ" => 'New Zealand', "NI" => 'Nicaragua', "NE" => 'Niger', "NG" => 'Nigeria', "NU" => 'Niue', "KP" => 'North Korea', "MP" => 'Northern Mariana Islands', "NO" => 'Norway', "ZZ" => 'OTHERS', "OM" => 'Oman', "PK" => 'Pakistan', "PW" => 'Palau', "PS" => 'Palestine', "PA" => 'Panama', "PG" => 'Papua New Guinea', "PY" => 'Paraguay', "PE" => 'Peru', "PH" => 'Philippines', "PN" => 'Pitcairn', "PL" => 'Poland', "PT" => 'Portugal', "PR" => 'Puerto Rico', "QA" => 'Qatar', "CG" => 'Republic of the Congo', "RE" => 'Reunion', "RO" => 'Romania', "RU" => 'Russia', "RW" => 'Rwanda', "BL" => 'Saint Barthelemy', "SH" => 'Saint Helena', "KN" => 'Saint Kitts and Nevis', "LC" => 'Saint Lucia', "MF" => 'Saint Martin', "PM" => 'Saint Pierre and Miquelon', "VC" => 'Saint Vincent and the Grenadines', "WS" => 'Samoa', "SM" => 'San Marino', "ST" => 'Sao Tome and Principe', "SA" => 'Saudi Arabia', "SN" => 'Senegal', "RS" => 'Serbia', "SC" => 'Seychelles', "SL" => 'Sierra Leone', "SX" => 'Sint Maarten', "SK" => 'Slovakia', "SI" => 'Slovenia', "SB" => 'Solomon Islands', "SO" => 'Somalia', "ZA" => 'South Africa', "KR" => 'South Korea', "SS" => 'South Sudan', "ES" => 'Spain', "LK" => 'Sri Lanka', "SD" => 'Sudan', "SR" => 'Suriname', "SJ" => 'Svalbard and Jan Mayen', "SZ" => 'Swaziland', "SE" => 'Sweden', "CH" => 'Switzerland', "SY" => 'Syria', "TW" => 'Taiwan', "TJ" => 'Tajikistan', "TZ" => 'Tanzania', "TH" => 'Thailand', "TG" => 'Togo', "TK" => 'Tokelau', "TO" => 'Tonga', "TT" => 'Trinidad and Tobago', "TN" => 'Tunisia', "TR" => 'Turkey', "TM" => 'Turkmenistan', "TC" => 'Turks and Caicos Islands', "TV" => 'Tuvalu', "VI" => 'U.S. Virgin Islands', "UG" => 'Uganda', "UA" => 'Ukraine', "AE" => 'United Arab Emirates', "GB" => 'United Kingdom', "US" => 'United States', "UY" => 'Uruguay', "UZ" => 'Uzbekistan', "VU" => 'Vanuatu', "VA" => 'Vatican', "VE" => 'Venezuela', "VN" => 'Vietnam', "WF" => 'Wallis and Futuna', "EH" => 'Western Sahara', "YU" => 'YUGOSLAVIA', "YE" => 'Yemen', "ZM" => 'Zambia', "ZW" => 'Zimbabwe'];
    const OCCUPATIONS = ['', 'Acupuncturist', 'Admin', 'Air Crew / Pilot', 'Air Traffic Controller', 'Airline Officer', 'Architect', 'ArtHandicraft/Antique Dealer', 'Astronomer', 'Author', 'Baby Sitter', 'Baker', 'Barber', 'Barrister', 'Bartender', 'Beautician', 'Boilerman', 'Butcher', 'Car Dealer', 'Carpenter (Woodworking Machinery)', 'Cashier', 'Caterer', 'Chamber Maid/Caretaker', 'Chauffeur', 'Cheer Leader', 'Chef/Cook/Confectioner', 'Chemical Plant Worker', 'Chiropractor', 'Cleaner (Indoor)', 'Cleaner (Office)', 'Cleaner(Outdoor, Exclude Height Works)', 'Clerical', 'Coach', 'Construction Engineer', 'Construction Worker / Supervisor/Foreman', 'Counseller', 'Craftman', 'Crane Operator', 'Customer Service (Indoor)', 'Customer Service (Outdoor)', 'Dancer/Dance Instructor', 'Deliveryman', 'Dentist', 'Despatch Rider', 'Dietician', 'Diplomat', 'Disc Jockey', 'Diver', 'Domestic Helper', 'Domestic Maid', 'Doormen', 'Draughtman', 'Driver Cum Delivery Man', 'Driver/Despatch Rider', 'Driving Instructor', 'Economist', 'EditorCopy Writer', 'Engineer (DeskBound)', 'Engineer (Involves Use Of Tool)', 'Engineer (Outdoor)', 'Entertainer', 'Executive(Indoor)', 'Executive(Outdoor)', 'Factory Operator', 'Farmer', 'Fashion Designer', 'Finance &amp; Accounts', 'Financial Advisor', 'Fisherman', 'Fishmonger', 'Fitness Trainer', 'Fitter', 'Florist', 'Forklift Driver', 'Gardener', 'Geologist', 'Glazier', 'Godown Keeper', 'Grocer', 'Hairstylist', 'Hawker Inspector', 'Hawker/Stallholder', 'Health Inspector', 'Homemaker/Housewife', 'Housekeeper', 'Interior Designer', 'Janitor', 'Jeweller', 'Jockeys', 'Journalist Indoor', 'Judge', 'Kitchen Assistant', 'Laboratory Technician', 'Lawyer', 'Lecturer', 'Librarian', 'Life Guard', 'Lift Attendant', 'Locksmith', 'Logistic Assistant', 'Machinist', 'Machinist (Woodworking)', 'Magician', 'Maintenance Inspector / Supervisor(Max Height Works @ 3M)', 'Maintenance Technician  (Max Height Works @ 3M)', 'Management/Administration', 'Marine Salvage Crew', 'Martial Arts Instructor', 'Masseur/ Masseuse', 'Mechanic', 'Medical Practitioner', 'Merchant', 'Meteorologist', 'Meter Reader', 'Midwife', 'Minister Of Singapore Government', 'Model', 'Money Changer', 'Motor Engineer', 'Musician', 'National Servicemen', 'News Vendor', 'Newscaster (Indoor)', 'Newscaster (Outdoor)', 'Novelist', 'Nurse', 'Nurse (Outpatient Clinic)', 'Odd Job Labourer', 'Oil Rig Worker', 'Onboard Vessel (Admin/Service Crew)', 'Onboard Vessel Work (Manual Work)', 'Operations Executive / Manager', 'Operator/Production', 'Optician', 'Others', 'Outward Bound Trainer', 'Packer', 'Painter_Indoor (Max 3 Meter Above Ground)', 'Painter_Outdoor (Max 10 Meter Above Ground)', 'Paramedics', 'Parking Officer', 'Pawnbroker', 'Person Involved In Handling Of Explosives', 'Petrol Pump Attendant', 'Pharmacist', 'Photographer(Indoor)', 'Photographer(Outdoor)', 'Piano Tuner', 'Planter', 'Plumber', 'Porter', 'Postman', 'Prison Officer / Warden', 'Private Investigator', 'Professional Sportsperson', 'Publisher/Printer', 'Purchaser (Indoor)', 'Purchaser (Outdoor)', 'QC Inspector', 'Radio/Television Engineer', 'Radiologist', 'Real Estate Agent', 'Receptionist', 'Referee', 'Remisier', 'Renovations Contractor', 'Retiree', 'Safety Inspector', 'Sailor', 'School Teacher/Principal', 'Secretary', 'Security Personnel_Armed', 'Security Personnel_Unarmed', 'Self-Employed (Indoor)', 'Self-Employed (Outdoor)', 'Service Engineer', 'Ship Crew', 'Shipmaster', 'Signwriter', 'Singapore Armed Forces Personnel', 'Singapore Civil Defense Force', 'Singapore Police Force', 'Site Coordinator', 'Social Escort', 'Software Engineer', 'Sole Proprietor', 'Sole Proprietor (Outdoor)', 'Sole Proprietor(Indoor)', 'Statistician', 'Steerman', 'Stevedore /  Dockworkers', 'Stock Broker', 'Student &amp; Child In Singapore', 'Student_Full Time', 'Student_Outside Of Singapore', 'Student_Part Time', 'Tailor/Seamstress', 'Taxi Driver', 'Therapist', 'Tiler', 'Tour Consultant / Guide', 'Trader', 'Traffic Warden', 'Translator', 'Tutor', 'Typist', 'Undertaker', 'Unemployed', 'Usher', 'Vegetable Seller', 'Veterinary Surgeon', 'Waiter/Waitress', 'Watchman', 'Web Designer', 'Welder', 'Window Cleaner', 'Working Onboard Non-Sailing Vessel', 'Zookeeper', 'Zoologist' ];

    protected $casts = [
        'expiry_date' => 'datetime',
        'start_date' => 'datetime',
    ];

    protected $dataTableColumns = [
        'id' => [
            'searchable' => false,
            'orderable' => true
        ],
        'ref_no' => [
            'searchable' => true,
            'orderable' => true
        ],
        'policy_no' => [
            'searchable' => true,
            'orderable' => true
        ],
        'price' => [
            'searchable' => true,
            'orderable' => true
        ],
        'start_date' => [
            'searchable' => true,
            'orderable' => true
        ],
        'expiry_date' => [
            'searchable' => true,
            'orderable' => true
        ],
        'status' => [
            'searchable' => true,
            'orderable' => true
        ],
        'created_at' => [
            'searchable' => true,
            'orderable' => true
        ],
        'insurer_id' => [
            'searchable' => true,
            'orderable' => true
        ]
    ];

    protected $dataTableRelationships = [
        "belongsTo" => [
            'vehicle' => [
                "model" => \App\Models\Vehicle::class,
                'foreign_key' => 'vehicle_id',
                'columns' => [
                    'registration_no' => [
                        'searchable' => true,
                        'orderable' => true,
                    ],
                ],
            ],
            'proposer' => [
                "model" => \App\Models\Proposer::class,
                'foreign_key' => 'proposer_id',
                'columns' => [
                    'name' => [
                        'searchable' => true,
                        'orderable' => true,
                    ],
                ],
            ],
            'dealer' => [
                "model" => \App\Models\Company::class,
                'foreign_key' => 'dealer_id',
                'columns' => [
                    'name' => [
                        'searchable' => true,
                        'orderable' => true,
                    ],
                ],
            ],
        ],
    ];

    public function dealer()
    {
        return $this->belongsTo(\App\Models\Company::class, 'dealer_id');
    }

    public function proposer()
    {
        return $this->belongsTo(\App\Models\Proposer::class, 'proposer_id');
    }

    public function driver()
    {
        return $this->belongsTo(\App\Models\Driver::class, 'driver_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(\App\Models\Vehicle::class, 'vehicle_id');
    }

    public function documents()
    {
        $user = Auth::user();
        if(Auth::guard('mobile')->check() || $user->category == 'dealer'){
            return $this->hasMany(\App\Models\MotorDocument::class)->where('type', '!=', 'debit_note');
        } else if($user->category == 'all_cars' || $user->category == 'insurer'){
            return $this->hasMany(\App\Models\MotorDocument::class);
        } else {
            return $this->hasMany(\App\Models\MotorDocument::class)
                        ->where('type', '!=', 'schedule')
                        ->where('type', '!=', 'ci')
                        ->where('type', '!=', 'tax_invoice')
                        ->where('type', '!=', 'debit_note')
                        ->where('type', '!=', 'policy')
                        ->where('type', '!=', 'log')
                        ->where('type', '!=', 'assessment');
        }
    }

    public function drivers()
    {
        return $this->hasMany(\App\Models\MotorDriver::class);
    }

    public function insurer()
    {
        return $this->belongsTo(\App\Models\Company::class, 'insurer_id');
    }

    public function getFormatStartDateAttribute()
    {
        if ($this->start_date != null) {
            return Carbon::parse($this->start_date)->format("d/m/Y");
        } else {
            return "-";
        }
    }
   

    public function getFormatValidTillAttribute()
    {
        if ($this->quote_valid_till != null) {
            return Carbon::parse($this->quote_valid_till)->format("d/m/Y");
        } else {
            return "-";
        }
    }

    public function getFormatSubmittedAtAttribute()
    {
        if ($this->submitted_at != null) {
            return Carbon::parse($this->submitted_at)->format("d/m/Y");
        } else {
            return "-";
        }
    }

    public function getFormatExpiryDateAttribute()
    {
        if ($this->expiry_date != null) {
            return Carbon::parse($this->expiry_date)->format("d/m/Y");
        } else {
            return "-";    
        }
    }   
   

    public function getCreatedAtAttribute($value)
    {
        if ($value != null) {
            return Carbon::parse($value)->format("d/m/Y");
        } else {
            return "-";
        }
    } 
   

    public function getFormatPriceAttribute()
    {
        if ($this->price != null) {
            return "$" . number_format($this->price, 2);
        } else {
            return "-";
        }
    } 
   

    public function transactions()
    {
        return $this->morphMany(Transaction::class, 'payment_for');
    }
}
