export interface SelectOption {
  id: number
  name: string
  code?: string
}

export interface MasterRecord {
  id: number
  name?: string | null
  code?: string | null
  fullName?: string | null
  description?: string | null
  model?: string | null
  size?: string | null
  [key: string]: string | number | null | undefined
}

export interface MasterField {
  key: string
  label: string
  placeholder?: string
  type?: "text" | "number"
}

export type MeasurementCondition = "sebelum_cuci" | "sesudah_cuci" | "setelah_bakar"
export type MeasurementKey = "h" | "l" | "pc_10mm" | "pc_middle" | "upper_palm_width" | "lower_palm_width" | "wc" | "sl_titik" | "fl_1" | "fl_2" | "fl_3" | "fl_4" | "fl_5" | "fc_1" | "fc_2" | "fc_3" | "fc_4" | "fc_5" | "fc_internal_1" | "fc_internal_2" | "fc_internal_3" | "fc_internal_4" | "fc_internal_5" | "lt" | "d1" | "d2" | "pt" | "span" | "kl" | "tt" | "prbt_t" | "m" | "beading_267" | "beading_222" | "tb" | "w"

export type MeasurementValues = Record<MeasurementKey, string | number | null>
export type MeasurementInputValues = Record<MeasurementKey, string | number>

export interface MeasurementResult extends MeasurementValues {
  id?: number
  condition: MeasurementCondition
  remarks: string | null
}

export interface MeasurementResultPayload extends MeasurementInputValues {
  condition: MeasurementCondition
  remarks: string
}

export interface FormPengukuran {
  id: number
  areaId: number
  masterMouldId: number
  modelSizeId: number
  customerId: number
  hariKe: string | null
  mesin: string | null
  tanggalOutputSample: string | null
  tanggalPengukuran: string | null
  tanggalNaikMould: string | null
  dibuatOleh: string | null
  diperiksaOleh: string | null
  status: "draft" | "menunggu_pemeriksaan" | "disetujui" | "ditolak"
  area: SelectOption | null
  masterMould: SelectOption | null
  modelSize: SelectOption | null
  customer: SelectOption | null
  results?: MeasurementResult[]
}

export interface FormPengukuranPayload {
  areaId: number | null
  masterMouldId: number | null
  modelSizeId: number | null
  customerId: number | null
  hariKe: string
  mesin: string
  tanggalOutputSample: string
  tanggalPengukuran: string
  tanggalNaikMould: string
  dibuatOleh: string
  diperiksaOleh: string
  status: FormPengukuran["status"]
  results: MeasurementResultPayload[]
}
