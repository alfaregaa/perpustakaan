<?php
/**
 * Base class untuk semua Model: menyediakan koneksi DB dan helper transaksi.
 */
abstract class Model
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    /**
     * Menjalankan beberapa query dalam satu transaksi.
     * Jika ada error (termasuk DomainException), semua perubahan dibatalkan.
     */
    protected function transaction(callable $callback)
    {
        $this->db->beginTransaction();
        try {
            $result = $callback();
            $this->db->commit();
            return $result;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }
}
