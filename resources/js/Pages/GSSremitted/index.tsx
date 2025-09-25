import React, { useState } from 'react';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import axios from 'axios';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'GSS Remitted',
    href: '/GSSremitted',
  },
];

export default function GSSRemittedPage({ gssremitted }: any) {
  const [data, setData] = useState(gssremitted || []);
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
      const res = await axios.post('/gssremitted/import', formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });

      if (res.data.success) {
        setShowModal(false);

        // ✅ Fetch updated data from API
        const updated = await axios.get('/api/gssremitted');
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
      <Head title="GSS Remitted" />

      <div className="flex justify-between items-center mb-4">
        <h1 className="text-xl font-bold">GSS Remitted</h1>
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
              <th className="border px-2 py-1">BPNO</th>
              <th className="border px-2 py-1">Last Name</th>
              <th className="border px-2 py-1">First Name</th>
              <th className="border px-2 py-1">MI</th>
              <th className="border px-2 py-1">Basic Monthly Salary</th>
              <th className="border px-2 py-1">Effectivity Date</th>
              <th className="border px-2 py-1">PS</th>
              <th className="border px-2 py-1">GS</th>
              <th className="border px-2 py-1">EC</th>
              <th className="border px-2 py-1">CONSOLOAN</th>
              <th className="border px-2 py-1">EMRGYLN</th>
              <th className="border px-2 py-1">PLREG</th>
              <th className="border px-2 py-1">GFAL</th>
              <th className="border px-2 py-1">MPL</th>
              <th className="border px-2 py-1">MPL LITE</th>
            </tr>
          </thead>
          <tbody>
            {data.length === 0 ? (
              <tr>
                <td colSpan={15} className="text-center py-4">No records found.</td>
              </tr>
            ) : (
              data.map((item: any) => (
                <tr key={item.id}>
                  <td className="border px-2 py-1">{item.bpno}</td>
                  <td className="border px-2 py-1">{item.last_name}</td>
                  <td className="border px-2 py-1">{item.first_name}</td>
                  <td className="border px-2 py-1">{item.mi}</td>
                  <td className="border px-2 py-1">{item.basic_monthly_salary}</td>
                  <td className="border px-2 py-1">{item.effectivity_date}</td>
                  <td className="border px-2 py-1">{item.ps}</td>
                  <td className="border px-2 py-1">{item.gs}</td>
                  <td className="border px-2 py-1">{item.ec}</td>
                  <td className="border px-2 py-1">{item.consoloan}</td>
                  <td className="border px-2 py-1">{item.emrglyn}</td>
                  <td className="border px-2 py-1">{item.plreg}</td>
                  <td className="border px-2 py-1">{item.gfal}</td>
                  <td className="border px-2 py-1">{item.mpl}</td>
                  <td className="border px-2 py-1">{item.mpl_lite}</td>
                </tr>
              ))
            )}
          </tbody>
        </table>
      </div>
    </AppLayout>
  );
}
