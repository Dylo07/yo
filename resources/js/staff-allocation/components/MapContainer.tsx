import React from 'react';
import { Section, Staff } from '../types';
import { SectionBox } from './SectionBox';

interface MapContainerProps {
  sections: Section[];
  staffList: Staff[];
  assignments: Map<number, string>;
  onDrop: (sectionId: string) => void;
  onDragOver: (e: React.DragEvent) => void;
}

export const MapContainer: React.FC<MapContainerProps> = ({
  sections,
  staffList,
  assignments,
  onDrop,
  onDragOver,
}) => {
  const getAssignedStaff = (sectionId: string): Staff[] => {
    return staffList.filter((staff) => assignments.get(staff.id) === sectionId);
  };

  return (
    <div className="flex-1 bg-slate-50 p-6 overflow-auto">
      <div className="mb-4 flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold text-gray-800">Hotel Floor Plan</h1>
          <p className="text-sm text-gray-500">Drag and drop staff members to assign them to locations</p>
        </div>
        <div className="flex items-center gap-4 text-xs">
          <div className="flex items-center gap-2">
            <div className="w-4 h-4 rounded bg-slate-600"></div>
            <span>Rooms</span>
          </div>
          <div className="flex items-center gap-2">
            <div className="w-4 h-4 rounded bg-blue-500"></div>
            <span>Kitchen</span>
          </div>
          <div className="flex items-center gap-2">
            <div className="w-4 h-4 rounded bg-orange-500"></div>
            <span>Dining</span>
          </div>
          <div className="flex items-center gap-2">
            <div className="w-4 h-4 rounded bg-purple-500"></div>
            <span>Hall</span>
          </div>
          <div className="flex items-center gap-2">
            <div className="w-4 h-4 rounded bg-green-500"></div>
            <span>Office</span>
          </div>
          <div className="flex items-center gap-2">
            <div className="w-4 h-4 rounded bg-cyan-400"></div>
            <span>Pool</span>
          </div>
        </div>
      </div>

      <div
        className="relative bg-white rounded-xl shadow-lg border border-gray-200"
        style={{
          width: '100%',
          maxWidth: '1200px',
          aspectRatio: '1200 / 800',
          margin: '0 auto',
        }}
      >
        <div className="absolute inset-0 p-6">
          {sections.map((section) => (
            <SectionBox
              key={section.id}
              section={section}
              assignedStaff={getAssignedStaff(section.id)}
              onDrop={onDrop}
              onDragOver={onDragOver}
            />
          ))}
        </div>
      </div>
    </div>
  );
};
