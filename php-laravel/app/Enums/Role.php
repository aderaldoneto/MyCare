<?php

namespace App\Enums;

enum Role: string
{
    case Admin = 'admin';
    case Manager = 'manager';
    case Receptionist = 'receptionist';
    case Doctor = 'doctor';
    case Nurse = 'nurse';
    case Dentist = 'dentist';
    case Psychologist = 'psychologist';
    case Nutritionist = 'nutritionist';
    case Physiotherapist = 'physiotherapist';
}
