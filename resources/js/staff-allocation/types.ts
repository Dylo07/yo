export enum SectionType {
  ROOM = 'ROOM',
  KITCHEN = 'KITCHEN',
  DINING = 'DINING',
  HALL = 'HALL',
  OFFICE = 'OFFICE',
  POOL = 'POOL',
}

export interface Staff {
  id: number;
  name: string;
  avatar: string;
  role: string;
  category: string;
}

export interface Section {
  id: string;
  name: string;
  type: SectionType;
  top: number;
  left: number;
  width: number;
  height: number;
}

export interface Assignment {
  staffId: number;
  sectionId: string;
}

export interface StaffByCategory {
  [category: string]: Staff[];
}
