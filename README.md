# Yaren Tur — Web Sitesi ve Yönetim Paneli

yarentur.com.tr için öğrenci taşımacılığı, personel taşımacılığı, tur ve transfer hizmetlerini tanıtan site ve yönetim paneli.

Saf PHP + SQLite ile yazıldı. Composer, Node veya ayrı bir MySQL veritabanı gerekmez; dosyaları hostinge yüklemek yeterli.

## Özellikler

**Site**
- Çok sayfalı yapı: Ana sayfa, Kurumsal (`hakkimizda.php`), Hizmetler (`hizmetler.php`), Online çözümler (`online-cozumler.php`), Araç filomuz (`filo.php`), İletişim ve teklif formu (`iletisim.php`)
- Ana sayfa: fotoğraflı banner, hizmetler, online çözümler özeti, kurumsal özet, teklif bandı
- Online ulaşım çözümleri tanıtım bölümü (firma ekranı ve mobil uygulama anlatımı; metinler ve ekran görüntüleri panelden değiştirilebilir)
- Her hizmet için ayrı detay sayfası (`hizmet.php?s=...`)
- Kurumsal teklif sihirbazı (3 adım: firma bilgileri, servis ihtiyacı ve vardiya düzeni, not ve gönderim); talepler panele düşer, isteğe bağlı e-posta ve Telegram bildirimi gider
- Kurumsal güven bölümü (panelden düzenlenen maddeler ve isteğe bağlı rakamlar)
- Referans vaka kartları ("3 vardiya, 14 araç" gibi çalışma özetiyle)
- İndirilebilir kurumsal tanıtım dosyası (PDF)
- Mobilde alt bar (Arayın / WhatsApp / Teklif alın), masaüstünde WhatsApp butonu
- Spam koruması (toplama işlemli güvenlik sorusu + gizli alan + IP başına sınır), CSRF koruması

**Yönetim paneli (`/admin`)**
- Özet ekranı: yanıt bekleyen ve son talepler
- Teklif talepleri: durum takibi (Yeni, Aranadı, Teklif verildi, Anlaşıldı, Olumsuz), iç not, arama, Excel'e (CSV) aktarma
- Banner görselleri: ana sayfa fotoğraflarını ekle, sırala, odak noktasını ayarla
- Hizmetler: ekle, düzenle, sırala, gizle, görsel yükle
- Araç filosu: kapasite, özellikler, fotoğraf
- Referanslar: kurum adı, logo, sektör, çalışma özeti ve başlangıç yılı; düzenleme
- Site ayarları: logo, hakkımızda fotoğrafı, telefon, WhatsApp, adres, harita, ana sayfa, online çözümler ve güven bölümü metinleri, tanıtım dosyası (PDF), Telegram bildirimi
- Hesabım: şifre değiştirme, yönetici ekleme
- Hatalı girişlerde 15 dakikalık kilit

## Gereksinimler

- PHP 8.1 veya üzeri
- PHP eklentileri: `pdo_sqlite`, `mbstring`, `fileinfo` (Hostinger'da varsayılan olarak açık)
- Apache veya LiteSpeed (`.htaccess` dosyaları `data/` ve `includes/` klasörlerini dışarıya kapatır)

## Hostinger'a kurulum

1. Repodan **Code → Download ZIP** ile dosyaları indirin.
2. hPanel → Dosya Yöneticisi → `public_html` içine yükleyip açın. (Eski WordPress sitesini silmeden önce yedek alın; isterseniz önce bir alt alan adında, örn. `yeni.yarentur.com.tr`, deneyin.)
3. `data/` ve `uploads/` klasörlerinin yazılabilir olduğundan emin olun (izin 755).
4. Tarayıcıda `alanadiniz/admin` adresine gidin. İlk açılışta **yönetici hesabı oluşturma** ekranı gelir; kullanıcı adı ve şifrenizi belirleyin.
5. Görselleri yükleyin:
   - **Site ayarları → Logo**: firma logosu
   - **Banner görselleri**: ana sayfanın üstündeki araç fotoğrafları
   - **Site ayarları → Görseller**: hakkımızda fotoğrafı

   (Alternatif: dosyaları Dosya Yöneticisi ile `assets/img/` içine `logo.png`, `banner-1.jpg`, `banner-2.jpg`, `about.jpg` adlarıyla koyabilirsiniz.)
6. Site ayarlarından adres, e-posta, WhatsApp ve haritayı girin. Örnek bilgiler girilene kadar panel uyarı gösterir.
7. SSL aktifse kök dizindeki `.htaccess` içinde HTTPS yönlendirme satırlarının başındaki `#` işaretlerini kaldırın.

Veritabanı (`data/yarentur.sqlite`) ilk açılışta örnek hizmet ve araçlarla otomatik oluşur.

### Google Haritalar

Google Haritalar'da firmayı bulun → **Paylaş → Harita yerleştir → HTML'yi kopyala**. Kopyaladığınız `<iframe ...>` kodunu olduğu gibi Site ayarlarındaki harita alanına yapıştırın; panel adresi kendisi ayıklar.

### Telegram bildirimi

Site ayarlarının en altındaki **Telegram bildirimi** bölümünde adımlar yazılıdır: @BotFather ile bot oluşturun, token'ı yapıştırın, bota mesaj atın, "Sohbet ID'sini bul" ve "Test mesajı gönder" butonlarını kullanın. Sunucunun dışarıya HTTPS bağlantısı (cURL veya allow_url_fopen) açık olmalıdır.

### E-posta bildirimi

Site ayarlarında "Yeni teklif bildirimlerinin gideceği e-posta" alanını doldurun. Bildirim PHP `mail()` ile gönderilir; Hostinger'da bu adresin alan adına ait bir e-posta hesabı olması teslim oranını artırır.

## Yedekleme

Tüm içerik iki yerde durur: `data/yarentur.sqlite` (metinler, talepler, kullanıcılar) ve `uploads/` (yüklenen görseller). Bu ikisini düzenli olarak indirmeniz yeterli. Bu dosyalar `.gitignore` ile repodan hariç tutulur.

## Güncelleme (otomatik yükleme)

Site Natro'da barınıyor. `main` dalına gönderilen her değişiklik GitHub Actions (`.github/workflows/deploy.yml`) ile FTP üzerinden `public_html/` klasörüne otomatik yüklenir; yalnızca değişen dosyalar gönderilir. `data/`, `uploads/` ve kök `.htaccess` yükleme dışında tutulur.

Gerekli secret'lar (Settings → Secrets and variables → Actions): `FTP_SERVER`, `FTP_USERNAME`, `FTP_PASSWORD`.

Elle yükleme yapılacaksa `data/` ve `uploads/` klasörlerinin üzerine yazmayın.

Veritabanı şeması kendiliğinden güncellenir: yeni sütunlar ve ayarlar ilk istekte mevcut verilere dokunmadan eklenir.

## Yerelde çalıştırma

```bash
php -S localhost:8000
```

Ardından `http://localhost:8000` (site) ve `http://localhost:8000/admin` (panel).

## Klasör yapısı

```
index.php            Ana sayfa
hakkimizda.php       Kurumsal
hizmetler.php        Hizmetler
online-cozumler.php  Online ulaşım çözümleri
filo.php             Araç filosu
iletisim.php         İletişim ve teklif formu
hizmet.php           Hizmet detay sayfası
teklif.php           Teklif formu işleyicisi
admin/               Yönetim paneli
includes/            Veritabanı, yardımcı fonksiyonlar, şablon parçaları (parts/)
assets/              CSS, JS, logo
data/                SQLite veritabanı (otomatik oluşur, dışarıya kapalı)
uploads/             Panelden yüklenen görseller
```
