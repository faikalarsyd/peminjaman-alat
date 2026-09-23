USE db_peminjaman_alat;

DROP TRIGGER IF EXISTS trg_detail_stok_guard;
DELIMITER $$
CREATE TRIGGER trg_detail_stok_guard BEFORE INSERT ON detail_peminjaman FOR EACH ROW
BEGIN
    IF NEW.jumlah < 1 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Jumlah alat harus lebih dari 0.';
    END IF;
END$$
DELIMITER ;
