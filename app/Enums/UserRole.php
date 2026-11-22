<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Receptionist = 'receptionist';
    case Doctor = 'doctor';
    case Patient = 'patient';
}
