import React, { useState } from 'react';
import { Staff, Section, StaffByCategory } from '../types';
import { StaffCard } from './StaffCard';
import { CATEGORY_COLORS } from '../data';

interface SidebarProps {
  staffByCategory: StaffByCategory;
  sections: Section[];
  assignments: Map<number, string>;
  onUnassign: (staffId: number) => void;
  onDragStart: (e: React.DragEvent, staff: Staff) => void;
}

export const Sidebar: React.FC<SidebarProps> = ({
  staffByCategory,
  sections,
  assignments,
  onUnassign,
  onDragStart,
}) => {
  const [searchTerm, setSearchTerm] = useState('');
  const [expandedCategories, setExpandedCategories] = useState<Set<string>>(
    new Set(Object.keys(staffByCategory))
  );

  const toggleCategory = (category: string) => {
    const newExpanded = new Set(expandedCategories);
    if (newExpanded.has(category)) {
      newExpanded.delete(category);
    } else {
      newExpanded.add(category);
    }
    setExpandedCategories(newExpanded);
  };

  const getAssignedSection = (staffId: number): Section | undefined => {
    const sectionId = assignments.get(staffId);
    return sectionId ? sections.find((s) => s.id === sectionId) : undefined;
  };

  const formatCategoryName = (category: string): string => {
    return category
      .split('_')
      .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
      .join(' ');
  };

  const filterStaff = (staffList: Staff[]): Staff[] => {
    if (!searchTerm) return staffList;
    return staffList.filter((staff) =>
      staff.name.toLowerCase().includes(searchTerm.toLowerCase())
    );
  };

  const categoryColor = (category: string) =>
    CATEGORY_COLORS[category] || CATEGORY_COLORS.default;

  return (
    <div className="w-80 bg-white border-r border-gray-200 flex flex-col h-full shadow-lg">
      <div className="p-4 border-b border-gray-200 bg-gradient-to-r from-blue-600 to-blue-700">
        <h2 className="text-lg font-bold text-white flex items-center gap-2">
          <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
          Staff Members
        </h2>
        <p className="text-blue-100 text-sm mt-1">Drag staff to assign locations</p>
      </div>

      <div className="p-3 border-b border-gray-200">
        <div className="relative">
          <input
            type="text"
            placeholder="Search staff..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            className="w-full pl-9 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          />
          <svg
            className="absolute left-3 top-2.5 w-4 h-4 text-gray-400"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              strokeLinecap="round"
              strokeLinejoin="round"
              strokeWidth={2}
              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
            />
          </svg>
        </div>
      </div>

      <div className="flex-1 overflow-y-auto">
        {Object.entries(staffByCategory).map(([category, staffList]) => {
          const filteredStaff = filterStaff(staffList);
          if (filteredStaff.length === 0 && searchTerm) return null;

          const isExpanded = expandedCategories.has(category);
          const assignedCount = filteredStaff.filter((s) => assignments.has(s.id)).length;

          return (
            <div key={category} className="border-b border-gray-100">
              <button
                onClick={() => toggleCategory(category)}
                className="w-full px-4 py-3 flex items-center justify-between hover:bg-gray-50 transition-colors"
              >
                <div className="flex items-center gap-2">
                  <svg
                    className={`w-4 h-4 text-gray-500 transition-transform ${
                      isExpanded ? 'rotate-90' : ''
                    }`}
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      strokeLinecap="round"
                      strokeLinejoin="round"
                      strokeWidth={2}
                      d="M9 5l7 7-7 7"
                    />
                  </svg>
                  <span className={`px-2 py-0.5 rounded-full text-xs font-medium ${categoryColor(category)}`}>
                    {formatCategoryName(category)}
                  </span>
                </div>
                <div className="flex items-center gap-2 text-xs text-gray-500">
                  <span>{assignedCount}/{filteredStaff.length} assigned</span>
                </div>
              </button>

              {isExpanded && (
                <div className="px-3 pb-3 space-y-2">
                  {filteredStaff.map((staff) => (
                    <StaffCard
                      key={staff.id}
                      staff={staff}
                      assignedSection={getAssignedSection(staff.id)}
                      onUnassign={onUnassign}
                      onDragStart={onDragStart}
                    />
                  ))}
                  {filteredStaff.length === 0 && (
                    <p className="text-sm text-gray-400 text-center py-2">
                      No staff in this category
                    </p>
                  )}
                </div>
              )}
            </div>
          );
        })}
      </div>

      <div className="p-4 border-t border-gray-200 bg-gray-50">
        <div className="text-xs text-gray-500 space-y-1">
          <div className="flex justify-between">
            <span>Total Staff:</span>
            <span className="font-medium">
              {Object.values(staffByCategory).flat().length}
            </span>
          </div>
          <div className="flex justify-between">
            <span>Assigned:</span>
            <span className="font-medium text-green-600">{assignments.size}</span>
          </div>
        </div>
      </div>
    </div>
  );
};
