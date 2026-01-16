import React from 'react';
import { Staff, Section } from '../types';
import { CATEGORY_COLORS } from '../data';

interface StaffCardProps {
  staff: Staff;
  assignedSection?: Section;
  onUnassign: (staffId: number) => void;
  onDragStart: (e: React.DragEvent, staff: Staff) => void;
}

export const StaffCard: React.FC<StaffCardProps> = ({
  staff,
  assignedSection,
  onUnassign,
  onDragStart,
}) => {
  const categoryColor = CATEGORY_COLORS[staff.category] || CATEGORY_COLORS.default;

  return (
    <div
      draggable
      onDragStart={(e) => onDragStart(e, staff)}
      className="flex items-center gap-3 p-3 bg-white rounded-lg border border-gray-200 shadow-sm cursor-grab hover:shadow-md hover:border-blue-300 transition-all duration-200 active:cursor-grabbing"
    >
      <div className="relative flex-shrink-0">
        <div className="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-semibold text-sm shadow-inner">
          {staff.avatar}
        </div>
        {assignedSection && (
          <div className="absolute -bottom-1 -right-1 w-4 h-4 bg-green-500 rounded-full border-2 border-white" />
        )}
      </div>
      
      <div className="flex-1 min-w-0">
        <p className="font-medium text-gray-900 text-sm truncate">{staff.name}</p>
        <span className={`inline-block px-2 py-0.5 rounded-full text-xs font-medium ${categoryColor}`}>
          {staff.role}
        </span>
      </div>

      {assignedSection && (
        <div className="flex flex-col items-end gap-1">
          <span className="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full truncate max-w-[80px]">
            {assignedSection.name}
          </span>
          <button
            onClick={(e) => {
              e.stopPropagation();
              onUnassign(staff.id);
            }}
            className="text-xs text-red-500 hover:text-red-700 hover:underline"
          >
            Unassign
          </button>
        </div>
      )}
    </div>
  );
};
