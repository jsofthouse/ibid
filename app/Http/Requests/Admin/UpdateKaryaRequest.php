<?php

namespace App\Http\Requests\Admin;

class UpdateKaryaRequest extends StoreKaryaRequest
{
    // Aturan validasi metadata karya sama persis dengan Store - tidak ada
    // field unik yang perlu di-ignore seperti pada Kategori.
}
