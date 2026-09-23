USE db_peminjaman_alat;

-- Dokumentasi stored procedure. Runtime PHP menggunakan transaksi PDO dan tidak memanggil CALL.
DROP FUNCTION IF EXISTS fn_hitung_denda;
DROP FUNCTION IF EXISTS fn_sp_denda;
DROP PROCEDURE IF EXISTS sp_ajukan_peminjaman;
DROP PROCEDURE IF EXISTS sp_setujui_peminjaman;
DROP PROCEDURE IF EXISTS sp_proses_pengembalian;
DROP PROCEDURE IF EXISTS sp_laporan_peminjaman;

DELIMITER $$

CREATE PROCEDURE sp_ajukan_peminjaman(
    IN p_user INT, IN p_pinjam DATE, IN p_rencana DATE,
    IN p_keperluan TEXT, IN p_catatan TEXT, IN p_alat INT, IN p_jumlah INT
)
BEGIN
    DECLARE stok INT DEFAULT 0;
    DECLARE kondisi_alat VARCHAR(50);
    DECLARE new_id BIGINT;
    DECLARE kode VARCHAR(40);
    START TRANSACTION;
    SELECT jumlah_tersedia,kondisi INTO stok,kondisi_alat FROM alat WHERE id=p_alat FOR UPDATE;
    IF stok IS NULL OR stok < p_jumlah THEN
        ROLLBACK;
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Stok alat tidak mencukupi.';
    END IF;
    SET kode=CONCAT('PJM-',DATE_FORMAT(NOW(),'%Y%m%d%H%i%s'),'-',LPAD(FLOOR(RAND()*999),3,'0'));
    INSERT INTO peminjaman(kode_peminjaman,user_id,tanggal_pinjam,tanggal_kembali_rencana,keperluan,catatan,status)
    VALUES(kode,p_user,p_pinjam,p_rencana,p_keperluan,p_catatan,'MENUNGGU');
    SET new_id=LAST_INSERT_ID();
    INSERT INTO detail_peminjaman(peminjaman_id,alat_id,jumlah,kondisi_saat_pinjam)
    VALUES(new_id,p_alat,p_jumlah,kondisi_alat);
    COMMIT;
    SELECT new_id AS id;
END$$

CREATE PROCEDURE sp_setujui_peminjaman(IN p_peminjaman BIGINT, IN p_petugas INT)
BEGIN
    DECLARE stok INT DEFAULT 0;
    DECLARE alat_id_x INT;
    DECLARE jumlah_x INT;
    DECLARE selesai INT DEFAULT 0;
    DECLARE c CURSOR FOR SELECT alat_id,jumlah FROM detail_peminjaman WHERE peminjaman_id=p_peminjaman;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET selesai=1;
    START TRANSACTION;
    IF NOT EXISTS(SELECT 1 FROM peminjaman WHERE id=p_peminjaman AND status='MENUNGGU') THEN
        ROLLBACK;
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Peminjaman tidak berstatus MENUNGGU.';
    END IF;
    OPEN c;
    loop_detail: LOOP
        FETCH c INTO alat_id_x,jumlah_x;
        IF selesai=1 THEN LEAVE loop_detail; END IF;
        SELECT jumlah_tersedia INTO stok FROM alat WHERE id=alat_id_x FOR UPDATE;
        IF stok < jumlah_x THEN
            CLOSE c;
            ROLLBACK;
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Stok alat tidak mencukupi.';
        END IF;
    END LOOP;
    CLOSE c;
    UPDATE peminjaman SET status='DISETUJUI',approved_by=p_petugas,approved_at=NOW(),updated_at=NOW() WHERE id=p_peminjaman;
    COMMIT;
END$$

CREATE FUNCTION fn_sp_denda(tanggal_kembali_rencana DATE,tanggal_kembali_actual DATE,denda_per_hari DECIMAL(12,2))
RETURNS DECIMAL(12,2) DETERMINISTIC
BEGIN
    DECLARE hari INT DEFAULT 0;
    IF tanggal_kembali_actual > tanggal_kembali_rencana THEN
        SET hari=DATEDIFF(tanggal_kembali_actual,tanggal_kembali_rencana);
    END IF;
    RETURN hari*denda_per_hari;
END$$

CREATE PROCEDURE sp_proses_pengembalian(
    IN p_peminjaman BIGINT, IN p_tanggal DATE, IN p_kondisi VARCHAR(100),
    IN p_catatan TEXT, IN p_user INT, IN p_status VARCHAR(20)
)
BEGIN
    DECLARE tarif DECIMAL(12,2) DEFAULT 5000;
    DECLARE rencana DATE;
    DECLARE hari INT DEFAULT 0;
    DECLARE denda DECIMAL(12,2) DEFAULT 0;
    DECLARE new_id BIGINT;
    SELECT tanggal_kembali_rencana INTO rencana FROM peminjaman
    WHERE id=p_peminjaman AND status IN ('DISETUJUI','DIPINJAM','TERLAMBAT');
    IF rencana IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Transaksi tidak dapat dikembalikan.';
    END IF;
    SELECT CAST(nilai AS DECIMAL(12,2)) INTO tarif FROM pengaturan WHERE kunci='denda_per_hari' LIMIT 1;
    SET hari=GREATEST(DATEDIFF(p_tanggal,rencana),0);
    SET denda=fn_sp_denda(rencana,p_tanggal,tarif);
    START TRANSACTION;
    INSERT INTO pengembalian(peminjaman_id,tanggal_pengembalian,terlambat_hari,denda,kondisi_alat,catatan,diproses_oleh,status)
    VALUES(p_peminjaman,p_tanggal,hari,denda,p_kondisi,p_catatan,p_user,p_status);
    SET new_id=LAST_INSERT_ID();
    COMMIT;
    SELECT new_id AS id;
END$$

CREATE PROCEDURE sp_laporan_peminjaman(IN p_from DATE,IN p_to DATE,IN p_status VARCHAR(30))
BEGIN
    SELECT p.kode_peminjaman,u.nama,p.tanggal_pinjam,p.tanggal_kembali_rencana,p.tanggal_kembali_actual,p.status,COALESCE(SUM(d.jumlah),0) total_item
    FROM peminjaman p JOIN users u ON u.id=p.user_id
    LEFT JOIN detail_peminjaman d ON d.peminjaman_id=p.id
    WHERE (p_from IS NULL OR p.tanggal_pinjam>=p_from)
      AND (p_to IS NULL OR p.tanggal_pinjam<=p_to)
      AND (p_status IS NULL OR p_status='' OR p.status=p_status)
    GROUP BY p.id ORDER BY p.id DESC;
END$$

DELIMITER ;
