USE db_peminjaman_alat;

DROP FUNCTION IF EXISTS fn_hitung_denda;
DELIMITER $$
CREATE FUNCTION fn_hitung_denda(
    tanggal_kembali_rencana DATE,
    tanggal_kembali_actual DATE,
    denda_per_hari DECIMAL(12,2)
) RETURNS DECIMAL(12,2)
DETERMINISTIC
BEGIN
    DECLARE hari INT DEFAULT 0;
    IF tanggal_kembali_actual > tanggal_kembali_rencana THEN
        SET hari = DATEDIFF(tanggal_kembali_actual, tanggal_kembali_rencana);
    END IF;
    RETURN hari * denda_per_hari;
END$$
DELIMITER ;
