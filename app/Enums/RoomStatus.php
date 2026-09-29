<?php

namespace App\Enums;

enum RoomStatus: string
{
    case Lobby = 'lobby';
    case InProgress = 'in_progress';
    case Finished = 'finished';
}
