// import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
// import { Head } from '@inertiajs/react';

// export default function Dashboard() {
//     return (
//         <AuthenticatedLayout
//             header={
//                 <h2 className="text-xl font-semibold leading-tight text-gray-800">
//                     Batch Upload
//                 </h2>
//             }
//         >
//             <Head title="Dashboard" />

//             <div className="py-12">
//                 <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
//                     <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
//                         <div className="p-6 text-gray-900">
//                             You're logged in!
//                         </div>
//                     </div>
//                 </div>
//             </div>
//         </AuthenticatedLayout>
//     );
// }

import React, { useState, useEffect } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import axios from 'axios';

export default function BatchUpload() {
    const [hasRedisData, setHasRedisData] = useState(false);
    const [plotsData, setPlotsData] = useState([]);

    // Check Redis and fetch MySQL data on component mount
    useEffect(() => {
        const checkData = async () => {
            try {
                const redisResponse = await axios.get('/check-redis-data');
                setHasRedisData(redisResponse.data.hasData);

                const plotsResponse = await axios.get('/get-plots');
                setPlotsData(plotsResponse.data.plots);
            } catch (error) {
                console.error('Error fetching data:', error);
            }
        };
        checkData();
    }, []);

    // Store Redis data into MySQL
    const handleStoreToMysql = async () => {
        try {
            const response = await axios.post('/store-into-mysql');
            alert(response.data.message);
            setHasRedisData(false);
        } catch (error) {
            console.error('Error storing data:', error);
            alert('Error storing data into MySQL');
        }
    };

    return (
        <AuthenticatedLayout
            header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Batch Upload</h2>}
        >
            <Head title="Batch Upload" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">

                            {/* Button to store data into MySQL if Redis has data */}
                            {hasRedisData ? (
                                <button
                                    onClick={handleStoreToMysql}
                                    className="px-4 py-2 mt-4 text-white bg-green-600 rounded-md hover:bg-green-700"
                                >
                                    Store Data into MySQL
                                </button>
                            ) : (
                                <p className="mt-4 text-gray-500">No data available in Redis.</p>
                            )}

                            {/* Display Data from Plots Table */}
                            {plotsData.length > 0 ? (
                                <div className="mt-8">
                                    <h2 className="text-lg font-semibold">Plots Data</h2>
                                    <table className="min-w-full border-collapse border border-gray-200 mt-4">
                                        <thead>
                                            <tr>
                                                <th className="border border-gray-300 p-2">ID</th>
                                                <th className="border border-gray-300 p-2">Plot Number</th>
                                                <th className="border border-gray-300 p-2">Block</th>
                                                <th className="border border-gray-300 p-2">Plot Size</th>
                                                <th className="border border-gray-300 p-2">Price (USD)</th>
                                                <th className="border border-gray-300 p-2">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {plotsData.map((plot, index) => (
                                                <tr key={index} className="hover:bg-gray-100">
                                                    <td className="border border-gray-300 p-2">{plot.id}</td>
                                                    <td className="border border-gray-300 p-2">{plot.plot_number}</td>
                                                    <td className="border border-gray-300 p-2">{plot.block}</td>
                                                    <td className="border border-gray-300 p-2">{plot.plot_size}</td>
                                                    <td className="border border-gray-300 p-2">${plot.price}</td>
                                                    <td className="border border-gray-300 p-2">{plot.status}</td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            ) : (
                                <p className="mt-8 text-gray-500">No data found in the plots table.</p>
                            )}

                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
