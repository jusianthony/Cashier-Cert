import React, { useState } from 'react';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import axios from 'axios';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'GSIS Remitted',
    href: '/remitted/index',
  },
];

export default function Dashboard({ remitted }: any) {
  const [data, setData] = useState(remitted || []);
  const [showModal, setShowModal] = useState(false);
  const [file, setFile] = useState<File | null>(null);

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setFile(e.target.files?.[0] || null);
  };

  const handleUpload = async () => {
    if (!file) return;

    const formData = new FormData();
    formData.append('file', file);

    try {
      const res = await axios.post('/remitted/import', formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });

      if (res.data.success) {
        setShowModal(false);

        // ✅ Fetch updated data from API without reloading the page
        const updated = await axios.get('/api/remitted');
        setData(updated.data.remitted);

        alert('Import successful!');
      } else {
        alert(res.data.message || 'Import failed.');
      }
    } catch (error) {
      console.error('Upload failed', error);
      alert('Import failed. Check the file and try again.');
    }
  };

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="GSIS Remitted" />

      <div className="flex justify-between items-center mb-4">
        <h1 className="text-xl font-bold">GSIS Remitted</h1>
        <button
          onClick={() => setShowModal(true)}
          className="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
        >
          Import
        </button>
      </div>

      {/* Modal */}
      {showModal && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
          <div className="bg-white p-6 rounded shadow-lg w-96">
            <h2 className="text-lg font-bold mb-4">Import Excel File</h2>
            <input type="file" accept=".xlsx, .xls" onChange={handleFileChange} />
            <div className="mt-4 flex justify-end gap-2">
              <button
                onClick={() => setShowModal(false)}
                className="px-4 py-2 bg-gray-300 rounded"
              >
                Cancel
              </button>
              <button
                onClick={handleUpload}
                className="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
              >
                Upload
              </button>
            </div>
          </div>
        </div>
      )}

      {/* Table */}
      <div className="overflow-x-auto">
        <table className="min-w-full text-sm border">
          <thead className="bg-gray-100">
            <tr>
              <th className="border px-2 py-1">COVERED DATE</th>
              <th className="border px-2 py-1">PAG-IBIG/ACCTNO/HLIDNO</th>
              <th className="border px-2 py-1">Employee ID</th>
              <th className="border px-2 py-1">Last Name</th>
              <th className="border px-2 py-1">First Name</th>
              <th className="border px-2 py-1">Middle Name</th>
              <th className="border px-2 py-1">Employee Contribution</th>
              <th className="border px-2 py-1">Employer Contribution</th>
              <th className="border px-2 py-1">TIN</th>
              <th className="border px-2 py-1">Birthdate</th>
              <th className="border px-2 py-1">OR No.</th>
              <th className="border px-2 py-1">Date</th>
            </tr>
          </thead>
          <tbody>
            {data.length === 0 ? (
              <tr>
                <td colSpan={13} className="text-center py-4">No records found.</td>
              </tr>
            ) : (
              data.map((item: any) => (
                <tr key={item.id}>
                  <td className="border px-2 py-1">{item.my_covered}</td>
                  <td className="border px-2 py-1">{item.pagibig_acctno}</td>
                  <td className="border px-2 py-1">{item.employee_id}</td>
                  <td className="border px-2 py-1">{item.last_name}</td>
                  <td className="border px-2 py-1">{item.first_name}</td>
                  <td className="border px-2 py-1">{item.middle_name}</td>
                  <td className="border px-2 py-1">{item.employee_contribution}</td>
                  <td className="border px-2 py-1">{item.employer_contribution}</td>
                  <td className="border px-2 py-1">{item.tin}</td>
                  <td className="border px-2 py-1">{item.birthdate}</td>
                  <td className="border px-2 py-1">{item.orno}</td>
                  <td className="border px-2 py-1">{item.date}</td>
                </tr>
              ))
            )}
          </tbody>
        </table>
      </div>
    </AppLayout>
  );
}
