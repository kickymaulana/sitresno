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
  label?: string | null
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

export type MeasurementCondition = string

export interface MeasurementConfigurationField {
  id: number
  parameterName: string
  condition: MeasurementCondition
  expectedValue: string | number | null
  description: string | null
  sortOrder: number
  isActive?: boolean
  label: string
  isStarred: boolean
}

export interface MeasurementConfiguration {
  id: number
  customerId: number
  modelSizeId: number
  isActive?: boolean
  fields: MeasurementConfigurationField[]
}

export interface MeasurementValue {
  configurationFieldId?: number | null
  parameterName?: string
  condition: MeasurementCondition
  specification?: string | number | null
  actual: string | number | null
  label?: string
  description?: string | null
  sortOrder?: number
  isStarred?: boolean
}

export interface FormPengukuranItem {
  id?: number
  modelSizeId: number | null
  modelSize?: SelectOption
  measurementConfigurationId?: number | null
  remarks: string | null
  actuals: MeasurementValue[]
}

export interface FormPengukuran {
  id: number
  areaId: number
  masterMouldId: number
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
  customer: SelectOption | null
  modelSizes: SelectOption[]
  items?: FormPengukuranItem[]
}

export interface FormPengukuranPayload {
  areaId: number | null
  masterMouldId: number | null
  customerId: number | null
  hariKe: string
  mesin: string
  tanggalOutputSample: string
  tanggalPengukuran: string
  tanggalNaikMould: string
  dibuatOleh: string
  diperiksaOleh: string
  status: FormPengukuran["status"]
  items: FormPengukuranItem[]
}
