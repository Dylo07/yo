import React, { useState } from 'react';
import { Section, Staff } from '../types';
import { SECTION_COLORS, LABEL_COLORS } from '../data';

interface SectionBoxProps {
  section: Section;
  assignedStaff: Staff[];
  onDrop: (sectionId: string) => void;
  onDragOver: (e: React.DragEvent) => void;
}

export const SectionBox: React.FC<SectionBoxProps> = ({
  section,
  assignedStaff,
  onDrop,
  onDragOver,
}) => {
  const [isDragOver, setIsDragOver] = useState(false);
  const colors = SECTION_COLORS[section.type];
  const labelColor = LABEL_COLORS[section.type];

  const handleDragOver = (e: React.DragEvent) => {
    e.preventDefault();
    setIsDragOver(true);
    onDragOver(e);
  };

  const handleDragLeave = () => {
    setIsDragOver(false);
  };

  const handleDrop = (e: React.DragEvent) => {
    e.preventDefault();
    setIsDragOver(false);
    onDrop(section.id);
  };

  const isSmallSection = section.width < 6 || section.height < 8;

  return (
    <div
      className="absolute"
      style={{
        top: `${section.top}%`,
        left: `${section.left}%`,
        width: `${section.width}%`,
        height: `${section.height}%`,
      }}
    >
      <span
        className={`absolute -top-5 left-0 text-xs font-semibold whitespace-nowrap ${labelColor}`}
        style={{ fontSize: isSmallSection ? '9px' : '11px' }}
      >
        {section.name}
      </span>
      
      <div
        onDragOver={handleDragOver}
        onDragLeave={handleDragLeave}
        onDrop={handleDrop}
        className={`
          w-full h-full rounded-md border-2 transition-all duration-200
          ${colors.bg} ${colors.border}
          ${isDragOver ? 'ring-4 ring-blue-400 ring-opacity-75 scale-105' : ''}
          flex flex-wrap items-center justify-center gap-1 p-1 overflow-hidden
        `}
      >
        {assignedStaff.length > 0 && (
          <div className="flex flex-wrap gap-0.5 items-center justify-center">
            {assignedStaff.slice(0, isSmallSection ? 3 : 6).map((staff, index) => (
              <div
                key={staff.id}
                className="w-6 h-6 rounded-full bg-white border-2 border-white shadow-md flex items-center justify-center text-xs font-bold text-gray-700"
                style={{
                  marginLeft: index > 0 ? '-8px' : '0',
                  zIndex: assignedStaff.length - index,
                }}
                title={staff.name}
              >
                {staff.avatar}
              </div>
            ))}
            {assignedStaff.length > (isSmallSection ? 3 : 6) && (
              <div
                className="w-6 h-6 rounded-full bg-gray-800 text-white text-xs flex items-center justify-center font-bold shadow-md"
                style={{ marginLeft: '-8px' }}
              >
                +{assignedStaff.length - (isSmallSection ? 3 : 6)}
              </div>
            )}
          </div>
        )}
        
        {assignedStaff.length === 0 && !isSmallSection && (
          <span className={`text-xs opacity-60 ${colors.text}`}>
            Drop staff here
          </span>
        )}
      </div>
    </div>
  );
};
