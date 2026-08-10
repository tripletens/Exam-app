<?php

namespace App\Enums;

enum ResourceType: string
{
    case YouTube = 'youtube';
    case Book = 'book';
    case Article = 'article';
    case Documentation = 'documentation';
    case PDF = 'pdf';
    case ExternalWebsite = 'external_website';
    case PracticalLab = 'practical_lab';
    case Assignment = 'assignment';
}
