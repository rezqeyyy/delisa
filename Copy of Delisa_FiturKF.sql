-- =========================
-- ENUM TYPES (sudah valid PG)
-- =========================
CREATE TYPE "kf_kesimpulan_pantauan_enum" AS ENUM ('Sehat','Dirujuk','Meninggal');

CREATE TYPE "kondisi_kesehatans_pemeriksaan_protein_urine_enum" AS ENUM (
  'Negatif','Positif 1','Positif 2','Positif 3','Belum dilakukan Pemeriksaan'
);

CREATE TYPE "kuisioner_pasiens_status_soal_enum" AS ENUM ('individu','keluarga','pre_eklampsia');

CREATE TYPE "kuisioner_pasiens_resiko_enum" AS ENUM ('non-risk','sedang','tinggi');

CREATE TYPE "riwayat_rujukans_anjuran_kontrol_enum" AS ENUM ('fktp','rs');

-- =============
-- TABLES
-- =============
CREATE TABLE "anak_pasien" (
  "id" BIGSERIAL PRIMARY KEY,
  "anak_ke" INTEGER NOT NULL,
  "tanggal_lahir" DATE NOT NULL,
  "jenis_kelamin" VARCHAR(255) NOT NULL,
  "nama_anak" VARCHAR(255) NOT NULL,
  "usia_kehamilan_saat_lahir" VARCHAR(255) NOT NULL,
  "berat_lahir_anak" NUMERIC(8,2) NOT NULL,
  "panjang_lahir_anak" NUMERIC(8,2) NOT NULL,
  "lingkar_kepala_anak" NUMERIC(8,2) NOT NULL,
  "memiliki_buku_kia" BOOLEAN NOT NULL DEFAULT FALSE,
  "buku_kia_bayi_kecil" BOOLEAN NOT NULL DEFAULT FALSE,
  "imd" BOOLEAN NOT NULL DEFAULT FALSE,
  "nifas_id" BIGINT NOT NULL,
  "created_at" TIMESTAMP,
  "updated_at" TIMESTAMP
);

CREATE TABLE "bidans" (
  "id" BIGSERIAL PRIMARY KEY,
  "user_id" BIGINT NOT NULL,
  "nomor_izin_praktek" VARCHAR(255) NOT NULL,
  "puskesmas_id" BIGINT NOT NULL,
  "created_at" TIMESTAMP,
  "updated_at" TIMESTAMP
);

CREATE TABLE "cache" (
  "key" VARCHAR(255) PRIMARY KEY,
  "value" TEXT NOT NULL,
  "expiration" INTEGER NOT NULL
);

CREATE TABLE "cache_locks" (
  "key" VARCHAR(255) PRIMARY KEY,
  "owner" VARCHAR(255) NOT NULL,
  "expiration" INTEGER NOT NULL
);

CREATE TABLE "failed_jobs" (
  "id" BIGSERIAL PRIMARY KEY,
  "uuid" VARCHAR(255) NOT NULL,
  "connection" TEXT NOT NULL,
  "queue" TEXT NOT NULL,
  "payload" TEXT NOT NULL,
  "exception" TEXT NOT NULL,
  "failed_at" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE "forgot_password_users" (
  "id" BIGSERIAL PRIMARY KEY,
  "email" VARCHAR(255) NOT NULL,
  "otp_code" VARCHAR(255) NOT NULL,
  "is_used" BOOLEAN NOT NULL,
  "created_at" TIMESTAMP,
  "updated_at" TIMESTAMP
);

CREATE TABLE "jawaban_kuisioners" (
  "id" BIGSERIAL PRIMARY KEY,
  "kuisioner_id" BIGINT NOT NULL,
  "jawaban" BOOLEAN NOT NULL DEFAULT FALSE,
  "jawaban_lainnya" VARCHAR(255),
  "skrining_id" BIGINT NOT NULL,
  "created_at" TIMESTAMP,
  "updated_at" TIMESTAMP
);

CREATE TABLE "jobs" (
  "id" BIGSERIAL PRIMARY KEY,
  "queue" VARCHAR(255) NOT NULL,
  "payload" TEXT NOT NULL,
  "attempts" SMALLINT NOT NULL,
  "reserved_at" INTEGER,
  "available_at" INTEGER NOT NULL,
  "created_at" INTEGER NOT NULL
);

CREATE TABLE "job_batches" (
  "id" VARCHAR(255) PRIMARY KEY,
  "name" VARCHAR(255) NOT NULL,
  "total_jobs" INTEGER NOT NULL,
  "pending_jobs" INTEGER NOT NULL,
  "failed_jobs" INTEGER NOT NULL,
  "failed_job_ids" TEXT NOT NULL,
  "options" TEXT,
  "cancelled_at" INTEGER,
  "created_at" INTEGER NOT NULL,
  "finished_at" INTEGER
);

CREATE TABLE "kf" (
  "id" BIGSERIAL PRIMARY KEY,
  "id_nifas" BIGINT NOT NULL,
  "id_anak" BIGINT NOT NULL,
  "kunjungan_nifas_ke" BIGINT NOT NULL,
  "tanggal_kunjungan" DATE NOT NULL DEFAULT CURRENT_DATE,
  "sbp" BIGINT NOT NULL,
  "dbp" BIGINT NOT NULL,
  "map" BIGINT NOT NULL,
  "keadaan_umum" VARCHAR(255),
  "tanda_bahaya" VARCHAR(255),
  "kesimpulan_pantauan" kf_kesimpulan_pantauan_enum NOT NULL,
  "updated_at" TIMESTAMP,
  "created_at" TIMESTAMP
);

CREATE TABLE "kondisi_kesehatans" (
  "id" BIGSERIAL PRIMARY KEY,
  "skrining_id" BIGINT NOT NULL,
  "tinggi_badan" INTEGER NOT NULL,
  "berat_badan_saat_hamil" NUMERIC(5,2) NOT NULL,
  "imt" DOUBLE PRECISION NOT NULL,
  "status_imt" VARCHAR(255) NOT NULL,
  "hpht" DATE,
  "tanggal_skrining" DATE NOT NULL,
  "usia_kehamilan" INTEGER NOT NULL,
  "tanggal_perkiraan_persalinan" DATE NOT NULL,
  "anjuran_kenaikan_bb" VARCHAR(255) NOT NULL,
  "created_at" TIMESTAMP,
  "updated_at" TIMESTAMP,
  "sdp" INTEGER NOT NULL DEFAULT 0,
  "dbp" INTEGER NOT NULL DEFAULT 0,
  "map" NUMERIC(5,2) NOT NULL DEFAULT 0,
  "pemeriksaan_protein_urine" kondisi_kesehatans_pemeriksaan_protein_urine_enum NOT NULL DEFAULT 'Belum dilakukan Pemeriksaan'
);

CREATE TABLE "kuisioner_pasiens" (
  "id" BIGSERIAL PRIMARY KEY,
  "nama_pertanyaan" VARCHAR(255) NOT NULL,
  "status_soal" kuisioner_pasiens_status_soal_enum NOT NULL,
  "resiko" kuisioner_pasiens_resiko_enum NOT NULL,
  "created_at" TIMESTAMP,
  "updated_at" TIMESTAMP
);

CREATE TABLE "migrations" (
  "id" INTEGER PRIMARY KEY,
  "migration" VARCHAR(255) NOT NULL,
  "batch" INTEGER NOT NULL
);

CREATE TABLE "pasiens" (
  "id" BIGSERIAL PRIMARY KEY,
  "user_id" BIGINT NOT NULL,
  "nik" VARCHAR(16) NOT NULL,
  "tempat_lahir" VARCHAR(50),
  "tanggal_lahir" DATE,
  "status_perkawinan" BOOLEAN,
  "PKecamatan" VARCHAR(255),
  "PKabupaten" VARCHAR(255),
  "PProvinsi" VARCHAR(255),
  "PPelayanan" VARCHAR(255),
  "PKarakteristik" VARCHAR(255),
  "PWilayah" VARCHAR(255),
  "kode_pos" VARCHAR(10),
  "rt" VARCHAR(255),
  "rw" VARCHAR(255),
  "pekerjaan" VARCHAR(50),
  "pendidikan" VARCHAR(20),
  "pembiayaan_kesehatan" VARCHAR(255),
  "golongan_darah" VARCHAR(255),
  "no_jkn" VARCHAR(255),
  "created_at" TIMESTAMP,
  "updated_at" TIMESTAMP
);

CREATE TABLE "pasien_nifas_bidan" (
  "id" BIGSERIAL PRIMARY KEY,
  "bidan_id" BIGINT NOT NULL,
  "pasien_id" BIGINT NOT NULL,
  "tanggal_mulai_nifas" DATE,
  "created_at" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE "pasien_nifas_rs" (
  "id" BIGSERIAL PRIMARY KEY,
  "rs_id" BIGINT NOT NULL,
  "pasien_id" BIGINT NOT NULL,
  "tanggal_mulai_nifas" DATE,
  "created_at" TIMESTAMP,
  "updated_at" TIMESTAMP
);

CREATE TABLE "password_reset_tokens" (
  "id" BIGSERIAL PRIMARY KEY,
  "email" VARCHAR(255) NOT NULL,
  "token" VARCHAR(255) NOT NULL,
  "created_at" TIMESTAMP
);

CREATE TABLE "personal_access_tokens" (
  "id" BIGSERIAL PRIMARY KEY,
  "tokenable_type" VARCHAR(255) NOT NULL,
  "tokenable_id" BIGINT NOT NULL,
  "name" VARCHAR(255) NOT NULL,
  "token" VARCHAR(64) NOT NULL,
  "abilities" TEXT,
  "last_used_at" TIMESTAMP,
  "expires_at" TIMESTAMP,
  "created_at" TIMESTAMP,
  "updated_at" TIMESTAMP
);

CREATE TABLE "puskesmas" (
  "id" BIGSERIAL PRIMARY KEY,
  "nama_puskesmas" VARCHAR(255) NOT NULL,
  "lokasi" TEXT NOT NULL,
  "kecamatan" VARCHAR(255) NOT NULL,
  "created_at" TIMESTAMP,
  "updated_at" TIMESTAMP,
  "is_mandiri" BOOLEAN NOT NULL DEFAULT FALSE
);

CREATE TABLE "resep_obats" (
  "id" BIGSERIAL PRIMARY KEY,
  "riwayat_rujukan_id" BIGINT NOT NULL,
  "resep_obat" VARCHAR(255),
  "dosis" VARCHAR(255),
  "penggunaan" VARCHAR(255),
  "created_at" TIMESTAMP,
  "updated_at" TIMESTAMP
);

CREATE TABLE "riwayat_kehamilans" (
  "id" BIGSERIAL PRIMARY KEY,
  "skrining_id" BIGINT NOT NULL,
  "pasien_id" BIGINT NOT NULL,
  "kehamilan" INTEGER NOT NULL,
  "tahun_kehamilan" INTEGER NOT NULL,
  "pengalaman_kehamilan" VARCHAR(255) NOT NULL,
  "berat_lahir" NUMERIC(5,2),
  "kondisi_bayi" VARCHAR(255),
  "jenis_persalinan" VARCHAR(255),
  "penolong_persalinan" VARCHAR(255),
  "komplikasi" VARCHAR(255) NOT NULL DEFAULT 'Tidak Ada',
  "created_at" TIMESTAMP,
  "updated_at" TIMESTAMP
);

CREATE TABLE "riwayat_kehamilan_gpas" (
  "id" BIGSERIAL PRIMARY KEY,
  "skrining_id" BIGINT NOT NULL,
  "pasien_id" BIGINT NOT NULL,
  "total_kehamilan" VARCHAR(255),
  "total_persalinan" VARCHAR(255),
  "total_abortus" VARCHAR(255),
  "created_at" TIMESTAMP,
  "updated_at" TIMESTAMP
);

CREATE TABLE "riwayat_penyakit_nifas" (
  "id" BIGSERIAL PRIMARY KEY,
  "nifas_id" BIGINT NOT NULL,
  "nama_penyakit" VARCHAR(255),
  "keterangan_penyakit_lain" TEXT,
  "created_at" TIMESTAMP,
  "updated_at" TIMESTAMP,
  "anak_pasien_id" BIGINT NOT NULL
);

CREATE TABLE "riwayat_rujukans" (
  "id" BIGSERIAL PRIMARY KEY,
  "rujukan_id" BIGINT NOT NULL,
  "skrining_id" BIGINT NOT NULL,
  "tanggal_datang" DATE,
  "tekanan_darah" VARCHAR(255),
  "anjuran_kontrol" riwayat_rujukans_anjuran_kontrol_enum,
  "kunjungan_berikutnya" VARCHAR(255),
  "created_at" TIMESTAMP,
  "updated_at" TIMESTAMP,
  "tindakan" VARCHAR(255)
);

CREATE TABLE "roles" (
  "id" BIGSERIAL PRIMARY KEY,
  "nama_role" VARCHAR(40) NOT NULL,
  "created_at" TIMESTAMP,
  "updated_at" TIMESTAMP
);

CREATE TABLE "rujukan_nifas" (
  "id" BIGSERIAL PRIMARY KEY,
  "id_kf" BIGINT NOT NULL,
  "status_rujukan" BOOLEAN NOT NULL,
  "created_at" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "tanggal_rujukan" DATE NOT NULL,
  "rs_id" BIGINT NOT NULL,
  "updated_at" TIMESTAMP
);

CREATE TABLE "rujukan_rs" (
  "id" BIGSERIAL PRIMARY KEY,
  "pasien_id" BIGINT NOT NULL,
  "rs_id" BIGINT NOT NULL,
  "done_status" BOOLEAN NOT NULL DEFAULT FALSE,
  "created_at" TIMESTAMP,
  "updated_at" TIMESTAMP,
  "skrining_id" BIGINT NOT NULL,
  "catatan_rujukan" TEXT,
  "is_rujuk" BOOLEAN
);

CREATE TABLE "rumah_sakits" (
  "id" BIGSERIAL PRIMARY KEY,
  "user_id" BIGINT NOT NULL,
  "nama" VARCHAR(255) NOT NULL,
  "lokasi" VARCHAR(255) NOT NULL,
  "kecamatan" VARCHAR(255) NOT NULL,
  "kelurahan" VARCHAR(255) NOT NULL,
  "created_at" TIMESTAMP,
  "updated_at" TIMESTAMP
);

CREATE TABLE "sessions" (
  "id" VARCHAR(255) PRIMARY KEY,
  "user_id" BIGINT,
  "ip_address" VARCHAR(45),
  "user_agent" TEXT,
  "payload" TEXT NOT NULL,
  "last_activity" INTEGER NOT NULL
);

CREATE TABLE "skrinings" (
  "id" BIGSERIAL PRIMARY KEY,
  "pasien_id" BIGINT NOT NULL,
  "puskesmas_id" BIGINT NOT NULL,
  "status_pre_eklampsia" VARCHAR(255),
  "jumlah_resiko_sedang" INTEGER,
  "jumlah_resiko_tinggi" INTEGER,
  "kesimpulan" VARCHAR(255),
  "step_form" INTEGER,
  "tindak_lanjut" BOOLEAN NOT NULL DEFAULT FALSE,
  "checked_status" BOOLEAN NOT NULL DEFAULT FALSE,
  "created_at" TIMESTAMP,
  "updated_at" TIMESTAMP
);

CREATE TABLE "users" (
  "id" BIGSERIAL PRIMARY KEY,
  "name" VARCHAR(255) NOT NULL,
  "email" VARCHAR(255),
  "password" VARCHAR(255),
  "photo" VARCHAR(255),
  "phone" VARCHAR(255),
  "address" VARCHAR(255),
  "status" BOOLEAN NOT NULL DEFAULT FALSE,
  "role_id" BIGINT NOT NULL,
  "remember_token" VARCHAR(100),
  "created_at" TIMESTAMP,
  "updated_at" TIMESTAMP
);

CREATE TABLE "wilayah_kerja" (
  "id" BIGSERIAL PRIMARY KEY,
  "nama_wilayah" VARCHAR(255) NOT NULL,
  "puskesmas_id" BIGINT NOT NULL,
  "created_at" TIMESTAMP,
  "updated_at" TIMESTAMP
);

-- =========================
-- FOREIGN KEYS
-- =========================
ALTER TABLE "anak_pasien"
  ADD CONSTRAINT "anak_pasien_nifas_id_foreign"
  FOREIGN KEY ("nifas_id") REFERENCES "pasiens" ("id") ON DELETE CASCADE;

ALTER TABLE "bidans"
  ADD CONSTRAINT "bidans_puskesmas_id_foreign"
  FOREIGN KEY ("puskesmas_id") REFERENCES "puskesmas" ("id") ON DELETE CASCADE;

ALTER TABLE "bidans"
  ADD CONSTRAINT "bidans_user_id_foreign"
  FOREIGN KEY ("user_id") REFERENCES "users" ("id") ON DELETE CASCADE;

ALTER TABLE "jawaban_kuisioners"
  ADD CONSTRAINT "jawaban_kuisioners_kuisioner_id_foreign"
  FOREIGN KEY ("kuisioner_id") REFERENCES "kuisioner_pasiens" ("id") ON DELETE CASCADE;

ALTER TABLE "jawaban_kuisioners"
  ADD CONSTRAINT "jawaban_kuisioners_skrining_id_foreign"
  FOREIGN KEY ("skrining_id") REFERENCES "skrinings" ("id") ON DELETE CASCADE;

ALTER TABLE "kf"
  ADD CONSTRAINT "Anak"
  FOREIGN KEY ("id_anak") REFERENCES "anak_pasien" ("id") ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE "kf"
  ADD CONSTRAINT "Nifas"
  FOREIGN KEY ("id_nifas") REFERENCES "pasiens" ("id") ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE "kondisi_kesehatans"
  ADD CONSTRAINT "kondisi_kesehatans_skrining_id_foreign"
  FOREIGN KEY ("skrining_id") REFERENCES "skrinings" ("id") ON DELETE CASCADE;

ALTER TABLE "pasiens"
  ADD CONSTRAINT "pasiens_user_id_foreign"
  FOREIGN KEY ("user_id") REFERENCES "users" ("id") ON DELETE CASCADE;

ALTER TABLE "pasien_nifas_bidan"
  ADD CONSTRAINT "bidan"
  FOREIGN KEY ("bidan_id") REFERENCES "puskesmas" ("id") ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE "pasien_nifas_bidan"
  ADD CONSTRAINT "pasien"
  FOREIGN KEY ("pasien_id") REFERENCES "pasiens" ("id") ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE "pasien_nifas_rs"
  ADD CONSTRAINT "pasien_nifas_rs_pasien_id_foreign"
  FOREIGN KEY ("pasien_id") REFERENCES "pasiens" ("id") ON DELETE CASCADE;

ALTER TABLE "pasien_nifas_rs"
  ADD CONSTRAINT "pasien_nifas_rs_rs_id_foreign"
  FOREIGN KEY ("rs_id") REFERENCES "rumah_sakits" ("id") ON DELETE CASCADE;

ALTER TABLE "resep_obats"
  ADD CONSTRAINT "resep_obats_riwayat_rujukan_id_foreign"
  FOREIGN KEY ("riwayat_rujukan_id") REFERENCES "riwayat_rujukans" ("id") ON DELETE CASCADE;

ALTER TABLE "riwayat_kehamilans"
  ADD CONSTRAINT "riwayat_kehamilans_pasien_id_foreign"
  FOREIGN KEY ("pasien_id") REFERENCES "pasiens" ("id") ON DELETE CASCADE;

ALTER TABLE "riwayat_kehamilans"
  ADD CONSTRAINT "riwayat_kehamilans_skrining_id_foreign"
  FOREIGN KEY ("skrining_id") REFERENCES "skrinings" ("id") ON DELETE CASCADE;

ALTER TABLE "riwayat_kehamilan_gpas"
  ADD CONSTRAINT "riwayat_kehamilan_gpas_pasien_id_foreign"
  FOREIGN KEY ("pasien_id") REFERENCES "pasiens" ("id") ON DELETE CASCADE;

ALTER TABLE "riwayat_kehamilan_gpas"
  ADD CONSTRAINT "riwayat_kehamilan_gpas_skrining_id_foreign"
  FOREIGN KEY ("skrining_id") REFERENCES "skrinings" ("id") ON DELETE CASCADE;

ALTER TABLE "riwayat_penyakit_nifas"
  ADD CONSTRAINT "riwayat_penyakit_nifas_anak_pasien_id_foreign"
  FOREIGN KEY ("anak_pasien_id") REFERENCES "anak_pasien" ("id") ON DELETE CASCADE;

ALTER TABLE "riwayat_penyakit_nifas"
  ADD CONSTRAINT "riwayat_penyakit_nifas_nifas_id_foreign"
  FOREIGN KEY ("nifas_id") REFERENCES "pasiens" ("id") ON DELETE CASCADE;

ALTER TABLE "riwayat_rujukans"
  ADD CONSTRAINT "riwayat_rujukans_rujukan_id_foreign"
  FOREIGN KEY ("rujukan_id") REFERENCES "rujukan_rs" ("id") ON DELETE CASCADE;

ALTER TABLE "riwayat_rujukans"
  ADD CONSTRAINT "riwayat_rujukans_skrining_id_foreign"
  FOREIGN KEY ("skrining_id") REFERENCES "skrinings" ("id") ON DELETE CASCADE;

ALTER TABLE "rujukan_nifas"
  ADD CONSTRAINT "KF and Rujukan"
  FOREIGN KEY ("id_kf") REFERENCES "kf" ("id") ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE "rujukan_nifas"
  ADD CONSTRAINT "rs_id"
  FOREIGN KEY ("rs_id") REFERENCES "rumah_sakits" ("id") ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE "rujukan_rs"
  ADD CONSTRAINT "rujukan_rs_pasien_id_foreign"
  FOREIGN KEY ("pasien_id") REFERENCES "pasiens" ("id") ON DELETE CASCADE;

ALTER TABLE "rujukan_rs"
  ADD CONSTRAINT "rujukan_rs_rs_id_foreign"
  FOREIGN KEY ("rs_id") REFERENCES "rumah_sakits" ("id") ON DELETE CASCADE;

ALTER TABLE "rujukan_rs"
  ADD CONSTRAINT "rujukan_rs_skrining_id_foreign"
  FOREIGN KEY ("skrining_id") REFERENCES "skrinings" ("id") ON DELETE CASCADE;

ALTER TABLE "rumah_sakits"
  ADD CONSTRAINT "rumah_sakits_user_id_foreign"
  FOREIGN KEY ("user_id") REFERENCES "users" ("id") ON DELETE CASCADE;

ALTER TABLE "skrinings"
  ADD CONSTRAINT "skrinings_pasien_id_foreign"
  FOREIGN KEY ("pasien_id") REFERENCES "pasiens" ("id") ON DELETE CASCADE;

ALTER TABLE "skrinings"
  ADD CONSTRAINT "skrinings_puskesmas_id_foreign"
  FOREIGN KEY ("puskesmas_id") REFERENCES "puskesmas" ("id") ON DELETE CASCADE;

ALTER TABLE "users"
  ADD CONSTRAINT "users_role_id_foreign"
  FOREIGN KEY ("role_id") REFERENCES "roles" ("id") ON DELETE CASCADE;

ALTER TABLE "wilayah_kerja"
  ADD CONSTRAINT "wilayah_kerja_puskesmas_id_foreign"
  FOREIGN KEY ("puskesmas_id") REFERENCES "puskesmas" ("id") ON DELETE CASCADE;
