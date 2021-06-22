<?php


namespace App\Repositories\Api;


use App\Models\Cx_Game_Cates;
use App\Models\Cx_Game_List;
use App\Models\Cx_System_Tips;

class IndexRepository
{

    protected $Cx_Game_Cates, $Cx_Game_List, $Cx_System_Tips;

    public function __construct
    (
        Cx_Game_Cates $cx_Game_Cates,
        Cx_Game_List $cx_Game_List,
        Cx_System_Tips $cx_System_Tips
    )
    {
        $this->Cx_Game_Cates = $cx_Game_Cates;
        $this->Cx_Game_List = $cx_Game_List;
        $this->Cx_System_Tips = $cx_System_Tips;
    }

    public function tips()
    {
        
    }

}
