import React, { useState, useEffect, useCallback } from 'react';
import { Staff, Section, StaffByCategory } from './types';
import { INITIAL_SECTIONS } from './data';
import { Sidebar } from './components/Sidebar';
import { MapContainer } from './components/MapContainer';

interface ApiStaff {
  id: number;
  name: string;
  staffCategory?: {
    category: string;
  };
}

interface ApiResponse {
  staffByCategory: Record<string, ApiStaff[]>;
  categoryNames: Record<string, string>;
}

const App: React.FC = () => {
  const [staffList, setStaffList] = useState<Staff[]>([]);
  const [staffByCategory, setStaffByCategory] = useState<StaffByCategory>({});
  const [sections] = useState<Section[]>(INITIAL_SECTIONS);
  const [assignments, setAssignments] = useState<Map<number, string>>(new Map());
  const [draggedStaff, setDraggedStaff] = useState<Staff | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    fetchStaffData();
  }, []);

  const fetchStaffData = async () => {
    try {
      setIsLoading(true);
      const response = await fetch('/api/staff-allocation/staff');
      
      if (!response.ok) {
        throw new Error('Failed to fetch staff data');
      }
      
      const data: ApiResponse = await response.json();
      
      const allStaff: Staff[] = [];
      const categorizedStaff: StaffByCategory = {};
      
      Object.entries(data.staffByCategory).forEach(([category, members]) => {
        const categoryName = data.categoryNames[category] || category;
        categorizedStaff[category] = members.map((member) => {
          const staff: Staff = {
            id: member.id,
            name: member.name,
            avatar: member.name.charAt(0).toUpperCase(),
            role: categoryName,
            category: category,
          };
          allStaff.push(staff);
          return staff;
        });
      });
      
      setStaffList(allStaff);
      setStaffByCategory(categorizedStaff);
      setError(null);
    } catch (err) {
      console.error('Error fetching staff:', err);
      setError('Failed to load staff data. Using demo data.');
      loadDemoData();
    } finally {
      setIsLoading(false);
    }
  };

  const loadDemoData = () => {
    const demoStaff: StaffByCategory = {
      front_office: [
        { id: 1, name: 'Kamal Perera', avatar: 'K', role: 'Front Office', category: 'front_office' },
        { id: 2, name: 'Nimal Silva', avatar: 'N', role: 'Front Office', category: 'front_office' },
        { id: 3, name: 'Saman Fernando', avatar: 'S', role: 'Front Office', category: 'front_office' },
      ],
      kitchen: [
        { id: 4, name: 'Ruwan Jayawardena', avatar: 'R', role: 'Kitchen', category: 'kitchen' },
        { id: 5, name: 'Priya Kumari', avatar: 'P', role: 'Kitchen', category: 'kitchen' },
        { id: 6, name: 'Chaminda Bandara', avatar: 'C', role: 'Kitchen', category: 'kitchen' },
        { id: 7, name: 'Lakshmi Devi', avatar: 'L', role: 'Kitchen', category: 'kitchen' },
      ],
      restaurant: [
        { id: 8, name: 'Anura Dissanayake', avatar: 'A', role: 'Restaurant', category: 'restaurant' },
        { id: 9, name: 'Malini Rathnayake', avatar: 'M', role: 'Restaurant', category: 'restaurant' },
        { id: 10, name: 'Sunil Wickramasinghe', avatar: 'S', role: 'Restaurant', category: 'restaurant' },
      ],
      housekeeping: [
        { id: 11, name: 'Kumari Fonseka', avatar: 'K', role: 'Housekeeping', category: 'housekeeping' },
        { id: 12, name: 'Dilani Senanayake', avatar: 'D', role: 'Housekeeping', category: 'housekeeping' },
        { id: 13, name: 'Ranjith Gunawardena', avatar: 'R', role: 'Housekeeping', category: 'housekeeping' },
        { id: 14, name: 'Pushpa Mendis', avatar: 'P', role: 'Housekeeping', category: 'housekeeping' },
      ],
      maintenance: [
        { id: 15, name: 'Gamini Rajapaksa', avatar: 'G', role: 'Maintenance', category: 'maintenance' },
        { id: 16, name: 'Wasantha Liyanage', avatar: 'W', role: 'Maintenance', category: 'maintenance' },
      ],
      garden: [
        { id: 17, name: 'Bandula Herath', avatar: 'B', role: 'Garden', category: 'garden' },
        { id: 18, name: 'Siripala Gunasekara', avatar: 'S', role: 'Garden', category: 'garden' },
      ],
      pool: [
        { id: 19, name: 'Ajith Kumarasinghe', avatar: 'A', role: 'Pool', category: 'pool' },
      ],
      laundry: [
        { id: 20, name: 'Sriyani Pathirana', avatar: 'S', role: 'Laundry', category: 'laundry' },
        { id: 21, name: 'Chandrika Weerasinghe', avatar: 'C', role: 'Laundry', category: 'laundry' },
      ],
    };

    const allStaff = Object.values(demoStaff).flat();
    setStaffList(allStaff);
    setStaffByCategory(demoStaff);
  };

  const handleDragStart = useCallback((e: React.DragEvent, staff: Staff) => {
    setDraggedStaff(staff);
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/plain', staff.id.toString());
  }, []);

  const handleDragOver = useCallback((e: React.DragEvent) => {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
  }, []);

  const handleDrop = useCallback((sectionId: string) => {
    if (draggedStaff) {
      setAssignments((prev) => {
        const newAssignments = new Map(prev);
        newAssignments.set(draggedStaff.id, sectionId);
        return newAssignments;
      });
      setDraggedStaff(null);
    }
  }, [draggedStaff]);

  const handleUnassign = useCallback((staffId: number) => {
    setAssignments((prev) => {
      const newAssignments = new Map(prev);
      newAssignments.delete(staffId);
      return newAssignments;
    });
  }, []);

  if (isLoading) {
    return (
      <div className="h-screen flex items-center justify-center bg-slate-50">
        <div className="text-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
          <p className="text-gray-600">Loading staff data...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="h-screen flex bg-slate-50">
      {error && (
        <div className="fixed top-4 right-4 bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-2 rounded-lg shadow-lg z-50">
          {error}
        </div>
      )}
      
      <Sidebar
        staffByCategory={staffByCategory}
        sections={sections}
        assignments={assignments}
        onUnassign={handleUnassign}
        onDragStart={handleDragStart}
      />
      
      <MapContainer
        sections={sections}
        staffList={staffList}
        assignments={assignments}
        onDrop={handleDrop}
        onDragOver={handleDragOver}
      />
    </div>
  );
};

export default App;
