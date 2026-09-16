"use client";

import { useState } from "react";
import { motion } from "framer-motion";
import Image from "next/image";
import Link from "next/link";
import { Plus, Edit, Trash2, Eye, Search, Filter } from "lucide-react";
import { AnimatedPage, AnimatedSection, staggerContainer, fadeUpItem } from "@/components/AnimatedPage";
import { MOCK_CARS } from "@/lib/mock-data";
import { cn, formatCurrency, CATEGORY_COLORS, CATEGORY_LABELS } from "@/lib/utils";
import toast from "react-hot-toast";

export default function AdminCarsPage() {
  const [cars, setCars] = useState(MOCK_CARS);
  const [search, setSearch] = useState("");
  const [deleteId, setDeleteId] = useState<string | null>(null);

  const filtered = cars.filter(
    (c) =>
      `${c.make} ${c.model}`.toLowerCase().includes(search.toLowerCase()) ||
      c.category.toLowerCase().includes(search.toLowerCase())
  );

  const toggleAvailability = (id: string) => {
    setCars((prev) =>
      prev.map((c) => (c.id === id ? { ...c, isAvailable: !c.isAvailable } : c))
    );
    toast.success("Availability updated");
  };

  const deleteCar = (id: string) => {
    setCars((prev) => prev.filter((c) => c.id !== id));
    setDeleteId(null);
    toast.success("Car removed from fleet");
  };

  return (
    <AnimatedPage className="min-h-screen pt-20">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {/* Header */}
        <AnimatedSection className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-8">
          <div>
            <p className="text-brand-400 text-sm font-semibold uppercase tracking-widest mb-1">Admin</p>
            <h1 className="font-display text-3xl font-bold text-white">Manage Fleet</h1>
            <p className="text-dark-400 mt-1">{cars.length} vehicles in your collection</p>
          </div>
          <Link href="/admin/cars/new" className="btn-primary gap-2">
            <Plus className="w-4 h-4" /> Add New Car
          </Link>
        </AnimatedSection>

        {/* Search */}
        <AnimatedSection className="mb-6">
          <div className="relative max-w-md">
            <Search className="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-dark-400" />
            <input
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              placeholder="Search your fleet..."
              className="input pl-12"
            />
          </div>
        </AnimatedSection>

        {/* Car Table */}
        <AnimatedSection>
          <div className="card overflow-hidden">
            <div className="overflow-x-auto">
              <table className="w-full text-sm">
                <thead>
                  <tr className="border-b border-dark-700">
                    {["Vehicle", "Category", "Price/Day", "Status", "Rating", "Actions"].map((h) => (
                      <th key={h} className="text-left text-xs font-semibold text-dark-400 uppercase tracking-wider px-5 py-4">
                        {h}
                      </th>
                    ))}
                  </tr>
                </thead>
                <tbody>
                  {filtered.map((car, i) => (
                    <motion.tr
                      key={car.id}
                      initial={{ opacity: 0 }}
                      animate={{ opacity: 1 }}
                      transition={{ delay: i * 0.04 }}
                      className="border-b border-dark-700/50 last:border-0 hover:bg-white/2 transition-colors"
                    >
                      {/* Vehicle */}
                      <td className="px-5 py-4">
                        <div className="flex items-center gap-3">
                          <div className="relative w-16 h-10 rounded-lg overflow-hidden shrink-0">
                            <Image src={car.images[0]} alt={car.make} fill className="object-cover" />
                          </div>
                          <div>
                            <p className="font-semibold text-white">{car.make} {car.model}</p>
                            <p className="text-xs text-dark-500">{car.year} · {car.color}</p>
                          </div>
                        </div>
                      </td>

                      {/* Category */}
                      <td className="px-5 py-4">
                        <span className={cn("badge border", CATEGORY_COLORS[car.category])}>
                          {CATEGORY_LABELS[car.category]}
                        </span>
                      </td>

                      {/* Price */}
                      <td className="px-5 py-4 font-semibold text-white">{formatCurrency(car.pricePerDay)}</td>

                      {/* Status toggle */}
                      <td className="px-5 py-4">
                        <button
                          onClick={() => toggleAvailability(car.id)}
                          className={cn(
                            "flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold border transition-colors",
                            car.isAvailable
                              ? "bg-green-500/10 text-green-400 border-green-500/20 hover:bg-green-500/20"
                              : "bg-red-500/10 text-red-400 border-red-500/20 hover:bg-red-500/20"
                          )}
                        >
                          <span className={cn("w-1.5 h-1.5 rounded-full", car.isAvailable ? "bg-green-400" : "bg-red-400")} />
                          {car.isAvailable ? "Available" : "Booked"}
                        </button>
                      </td>

                      {/* Rating */}
                      <td className="px-5 py-4 text-white">
                        ⭐ {car.rating} <span className="text-dark-500">({car.reviewCount})</span>
                      </td>

                      {/* Actions */}
                      <td className="px-5 py-4">
                        <div className="flex items-center gap-1">
                          <Link
                            href={`/cars/${car.id}`}
                            className="p-2 rounded-lg text-dark-400 hover:text-white hover:bg-white/5 transition-colors"
                            title="View"
                          >
                            <Eye className="w-4 h-4" />
                          </Link>
                          <Link
                            href={`/admin/cars/${car.id}/edit`}
                            className="p-2 rounded-lg text-dark-400 hover:text-white hover:bg-white/5 transition-colors"
                            title="Edit"
                          >
                            <Edit className="w-4 h-4" />
                          </Link>
                          <button
                            onClick={() => setDeleteId(car.id)}
                            className="p-2 rounded-lg text-dark-400 hover:text-red-400 hover:bg-red-500/5 transition-colors"
                            title="Delete"
                          >
                            <Trash2 className="w-4 h-4" />
                          </button>
                        </div>
                      </td>
                    </motion.tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        </AnimatedSection>
      </div>

      {/* Delete Confirm Modal */}
      {deleteId && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
          <motion.div
            initial={{ scale: 0.9, opacity: 0 }}
            animate={{ scale: 1, opacity: 1 }}
            className="card p-6 max-w-sm w-full"
          >
            <div className="w-12 h-12 rounded-full bg-red-500/10 flex items-center justify-center mb-4">
              <Trash2 className="w-6 h-6 text-red-400" />
            </div>
            <h3 className="font-display text-lg font-bold text-white mb-2">Remove Car?</h3>
            <p className="text-dark-400 text-sm mb-6">
              This will permanently remove the car from your fleet. This action cannot be undone.
            </p>
            <div className="flex gap-3">
              <button onClick={() => setDeleteId(null)} className="btn-secondary flex-1 justify-center py-2.5">
                Cancel
              </button>
              <button
                onClick={() => deleteCar(deleteId)}
                className="flex-1 py-2.5 rounded-xl bg-red-500 hover:bg-red-600 text-white font-semibold transition-colors"
              >
                Remove
              </button>
            </div>
          </motion.div>
        </div>
      )}
    </AnimatedPage>
  );
}
