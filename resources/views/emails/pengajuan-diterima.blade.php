<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: Arial, sans-serif; color:#20241F; background:#F7F4EC; padding:24px; margin:0;">
    <div style="max-width:480px;margin:0 auto;background:#FFFFFF;border:1px solid #E5E0CF;border-radius:8px;padding:24px;">
        <p style="font-size:12px;letter-spacing:0.1em;text-transform:uppercase;color:#B08D57;margin:0 0 16px;">
            Irfani Book Identity
        </p>

        <h1 style="font-size:20px;color:#17352A;margin:0 0 16px;">Pengajuan Penerbitan Diterima</h1>

        <p>Halo {{ $pengajuan->nama }},</p>

        <p>
            Terima kasih, pengajuan penerbitan untuk naskah <strong>{{ $pengajuan->judul }}</strong>
            sudah kami terima dan akan diproses oleh tim Penerbit Irfani.
        </p>

        <p>
            Status pengajuan saat ini: <strong>Baru</strong>. Kami akan menghubungi Anda melalui email ini
            untuk perkembangan selanjutnya.
        </p>

        <p style="margin-top:24px;font-size:13px;color:#666;">
            Penerbit Irfani &middot; bukuirfani@gmail.com &middot; www.penerbitirfani.com
        </p>
    </div>
</body>
</html>
