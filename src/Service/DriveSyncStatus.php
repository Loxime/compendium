<?php
namespace App\Service;
final class DriveSyncStatus
{
    public function __construct(){}
    public function enabled(): bool { return filter_var($_ENV['GOOGLE_DRIVE_ENABLED']??false,FILTER_VALIDATE_BOOL); }
    public function missingConfiguration(): array { $missing=[]; foreach(['GOOGLE_DRIVE_CLIENT_ID','GOOGLE_DRIVE_CLIENT_SECRET','GOOGLE_DRIVE_FOLDER_ID'] as $k){if(empty($_ENV[$k]))$missing[]=$k;} return $missing; }
}
