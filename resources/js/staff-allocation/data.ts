import { Section, SectionType } from './types';

export const INITIAL_SECTIONS: Section[] = [
  // Top row rooms
  { id: 'ahela', name: 'Ahela', type: SectionType.ROOM, top: 3, left: 17, width: 4, height: 6 },
  { id: 'orchid-room', name: 'Orchid', type: SectionType.ROOM, top: 8, left: 17, width: 4, height: 6 },
  { id: 'sudu-araliya', name: 'Sudu Araliya', type: SectionType.ROOM, top: 8, left: 30, width: 6, height: 6 },
  { id: 'sepalika', name: 'Sepalika', type: SectionType.ROOM, top: 3, left: 38, width: 6, height: 6 },
  
  // Room numbers 121-124
  { id: 'room-121', name: '121', type: SectionType.ROOM, top: 3, left: 52, width: 3.5, height: 5 },
  { id: 'room-122', name: '122', type: SectionType.ROOM, top: 3, left: 56, width: 3.5, height: 5 },
  { id: 'room-123', name: '123', type: SectionType.ROOM, top: 3, left: 60, width: 3.5, height: 5 },
  { id: 'room-124', name: '124', type: SectionType.ROOM, top: 3, left: 64, width: 3.5, height: 5 },
  
  // Room numbers 106-109
  { id: 'room-109', name: '109', type: SectionType.ROOM, top: 11, left: 52, width: 3.5, height: 5 },
  { id: 'room-108', name: '108', type: SectionType.ROOM, top: 11, left: 56, width: 3.5, height: 5 },
  { id: 'room-107', name: '107', type: SectionType.ROOM, top: 11, left: 60, width: 3.5, height: 5 },
  { id: 'room-106', name: '106', type: SectionType.ROOM, top: 11, left: 64, width: 3.5, height: 5 },
  
  // Hansa
  { id: 'hansa', name: 'Hansa', type: SectionType.ROOM, top: 6, left: 88, width: 6, height: 8 },
  
  // Olu & Nelum
  { id: 'olu', name: 'Olu', type: SectionType.ROOM, top: 18, left: 22, width: 6, height: 6 },
  { id: 'nelum', name: 'Nelum', type: SectionType.ROOM, top: 24, left: 22, width: 6, height: 6 },
  
  // Kitchen-1
  { id: 'kitchen-1', name: 'Kitchen-1', type: SectionType.KITCHEN, top: 18, left: 56, width: 8, height: 12 },
  
  // Villa
  { id: 'villa', name: 'Villa', type: SectionType.ROOM, top: 18, left: 72, width: 10, height: 12 },
  
  // Swimming Pool
  { id: 'swimming-pool', name: 'Swimming Pool', type: SectionType.POOL, top: 34, left: 14, width: 18, height: 10 },
  
  // Hut-1
  { id: 'hut-1', name: 'Hut-1', type: SectionType.ROOM, top: 34, left: 44, width: 5, height: 6 },
  
  // Main Restaurant
  { id: 'main-restaurant', name: 'Main Restaurant', type: SectionType.DINING, top: 34, left: 56, width: 14, height: 14 },
  
  // Hut-3 & Hut-2
  { id: 'hut-3', name: 'Hut-3', type: SectionType.ROOM, top: 50, left: 16, width: 5, height: 6 },
  { id: 'hut-2', name: 'Hut-2', type: SectionType.ROOM, top: 50, left: 28, width: 5, height: 6 },
  
  // Room numbers 130-134
  { id: 'room-130', name: '130', type: SectionType.ROOM, top: 60, left: 1, width: 3.5, height: 5 },
  { id: 'room-131', name: '131', type: SectionType.ROOM, top: 60, left: 5, width: 3.5, height: 5 },
  { id: 'room-132', name: '132', type: SectionType.ROOM, top: 60, left: 9, width: 3.5, height: 5 },
  { id: 'room-133', name: '133', type: SectionType.ROOM, top: 60, left: 13, width: 3.5, height: 5 },
  { id: 'room-134', name: '134', type: SectionType.ROOM, top: 60, left: 17, width: 3.5, height: 5 },
  
  // Orchid Hall
  { id: 'orchid-hall', name: 'Orchid Hall', type: SectionType.HALL, top: 60, left: 86, width: 10, height: 18 },
  
  // Kitchen-2
  { id: 'kitchen-2', name: 'Kitchen-2', type: SectionType.KITCHEN, top: 70, left: 1, width: 4, height: 16 },
  
  // Banquet Hall
  { id: 'banquet-hall', name: 'Banquet Hall', type: SectionType.HALL, top: 70, left: 6, width: 18, height: 16 },
  
  // CH Room
  { id: 'ch-room', name: 'CH Room', type: SectionType.ROOM, top: 72, left: 18, width: 6, height: 6 },
  
  // Lihini
  { id: 'lihini', name: 'Lihini', type: SectionType.ROOM, top: 74, left: 34, width: 5, height: 6 },
  
  // Mayura
  { id: 'mayura', name: 'Mayura', type: SectionType.ROOM, top: 68, left: 40, width: 6, height: 6 },
  
  // Front Office
  { id: 'front-office', name: 'Front Office', type: SectionType.OFFICE, top: 64, left: 52, width: 24, height: 12 },
];

export const SECTION_COLORS: Record<SectionType, { bg: string; border: string; text: string }> = {
  [SectionType.ROOM]: { bg: 'bg-slate-600', border: 'border-slate-700', text: 'text-slate-200' },
  [SectionType.KITCHEN]: { bg: 'bg-blue-500', border: 'border-blue-600', text: 'text-blue-100' },
  [SectionType.DINING]: { bg: 'bg-orange-500', border: 'border-orange-600', text: 'text-orange-100' },
  [SectionType.HALL]: { bg: 'bg-purple-500', border: 'border-purple-600', text: 'text-purple-100' },
  [SectionType.OFFICE]: { bg: 'bg-green-500', border: 'border-green-600', text: 'text-green-100' },
  [SectionType.POOL]: { bg: 'bg-cyan-400', border: 'border-cyan-500', text: 'text-cyan-900' },
};

export const LABEL_COLORS: Record<SectionType, string> = {
  [SectionType.ROOM]: 'text-slate-700',
  [SectionType.KITCHEN]: 'text-blue-600',
  [SectionType.DINING]: 'text-orange-600',
  [SectionType.HALL]: 'text-purple-600',
  [SectionType.OFFICE]: 'text-green-600',
  [SectionType.POOL]: 'text-cyan-600',
};

export const CATEGORY_COLORS: Record<string, string> = {
  front_office: 'bg-green-100 text-green-800',
  garden: 'bg-lime-100 text-lime-800',
  kitchen: 'bg-blue-100 text-blue-800',
  maintenance: 'bg-yellow-100 text-yellow-800',
  restaurant: 'bg-orange-100 text-orange-800',
  housekeeping: 'bg-pink-100 text-pink-800',
  laundry: 'bg-indigo-100 text-indigo-800',
  pool: 'bg-cyan-100 text-cyan-800',
  default: 'bg-gray-100 text-gray-800',
};
