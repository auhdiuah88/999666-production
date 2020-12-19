<?php
/**
 * Created by PhpStorm.
 * User: ck
 * Date: 2020-12-19
 * Time: 16:42
 */

namespace App\Repositories\Admin\agent;


use App\Models\Cx_Admin;
use App\Models\Cx_Game_Betting;

class AgentBettingRepository
{
    private $Cx_Admin, $Cx_Game_Betting;

    public function __construct(Cx_Admin $Cx_Admin, Cx_Game_Betting $Cx_Game_Betting)
    {
        $this->Cx_Admin = $Cx_Admin;
        $this->Cx_Game_Betting = $Cx_Game_Betting;
    }

    public function findAll($offset, $limit)
    {
        $bettings = $this->Cx_Game_Betting->all();
        dd($bettings);
    }

}
