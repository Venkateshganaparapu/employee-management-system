"use client";

import { motion } from "framer-motion";
import Link from "next/link";
import Image from "next/image";
import {
  Car,
  DollarSign,
  Users,
  TrendingUp,
  Plus,
  Eye,
  Edit,
  Trash2,
  AlertTriangle,
  CheckCircle2,
  Clock,
} from "lucide-react";
import { AnimatedPage, AnimatedSection, staggerContainer, fadeUpItem } from "@/components/AnimatedPage";
import { MOCK_CARS } from "@/lib/mock-data";
import { cn, formatCurrency } from "@/lib/utils";

const STATS = [
  { label: "Total Revenue", value: formatCurrency(28460), change: "+12.5%", positive: true, icon: DollarSign, color: "text-green-400", bg: "bg-green-500/10" },
  { label: "Active Bookings", value: "14", change: "+3 this week", positive: true, icon: Car, color: "text-brand-400", bg: "bg-brand-500/10" },
  { label: "Total Renters", value: "286", change: "+28 this month", positive: true, icon: Users, color: "text-blue-400", bg: "bg-blue-500/10" },
  { label: "Fleet Utilization", value: "78%", change: "+5% vs last month", positive: true, icon: TrendingUp, color: "text-purple-400", bg: "bg-purple-500/10" },
];

const RECENT_BOOKINGS = [
  { id: "BK-A91", user: "Sarah Johnson", car: "Tesla Model S", days: 3, total: 824, status: "CONFIRMED" },
  { id: "BK-B72", user: "Marcus Chen", car: "Porsche 911", days: 2, total: 769, status: "ACTIVE" },
  { id: "BK-C44", user: "Priya Patel", car: "Range Rover Sport", days: 5, total: 1534, status: "PENDING" },
  { id: "BK-D18", user: "Jordan Lee", car: "BMW M5", days: 1, total: 329, status: "COMPLETED" },
];

const STATUS_COLORS: Record<string, string> = {
  PENDING:   "bg-yellow-500/10 text-yellow-400 border-yellow-500/20",
  CONFIRMED: "bg-blue-500/10 text-blue-400 border-blue-500/20",
  ACTIVE:    "bg-brand-500/10 text-brand-400 border-brand-500/20",
  COMPLETED: "bg-green-500/10 text-green-400 border-green-500/20",
  CANCELLED: "bg-red-500/10 text-red-400 border-red-500/20",
};

export default function AdminDashboard() {
  const availableCount = MOCK_CARS.filter(c => c.isAvailable).length;
  const unavailableCount = MOCK_CARS.length - availableCount;

  return (
    <AnimatedPage className="min-h-screen pt-20">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {/* Header */}
        <AnimatedSection className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-8">
          <div>
            <p className="text-brand-400 text-sm font-semibold uppercase tracking-widest mb-1">Admin Panel</p>
            <h1 className="font-display text-3xl font-bold text-white">Dashboard</h1>
          </div>
          <Link href="/admin/cars/new" className="btn-primary gap-2">
            <Plus className="w-4 h-4" /> Add New Car
          </Link>
        </AnimatedSection>

        {/* ── Stat Cards ── */}
        <motion.div
          variants={staggerContainer}
          initial="initial"
          animate="animate"
          className="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-8"
        >
          {STATS.map((stat) => (
            <motion.div key={stat.label} variants={fadeUpItem} className="card p-5">
              <div className="flex items-start justify-between mb-4">
                <div className={cn("w-10 h-10 rounded-xl flex items-center justify-center", stat.bg)}>
                  <stat.icon className={cn("w-5 h-5", stat.color)} />
                </div>
                <span className={cn("text-xs font-medium", stat.positive ? "text-green-400" : "text-red-400")}>
                  {stat.change}
                </span>
              </div>
              <p className="font-display text-2xl font-bold text-white mb-1">{stat.value}</p>
              <p className="text-dark-400 text-sm">{stat.label}</p>
            </motion.div>
          ))}
        </motion.div>

        <div className="grid grid-cols-1 xl:grid-cols-3 gap-8">
          {/* ── Recent Bookings ── */}
          <div className="xl:col-span-2">
            <AnimatedSection>
              <div className="flex items-center justify-between mb-5">
                <h2 className="font-display text-lg font-bold text-white">Recent Bookings</h2>
                <Link href="/admin/bookings" className="text-sm text-brand-400 hover:text-brand-300 transition-colors">
                  View all →
                </Link>
              </div>
              <div className="card overflow-hidden">
                <table className="w-full text-sm">
                  <thead>
                    <tr className="border-b border-dark-700">
                      <th className="text-left text-xs font-semibold text-dark-400 uppercase tracking-wider px-5 py-3">Booking</th>
                      <th className="text-left text-xs font-semibold text-dark-400 uppercase tracking-wider px-5 py-3 hidden sm:table-cell">Car</th>
                      <th className="text-left text-xs font-semibold text-dark-400 uppercase tracking-wider px-5 py-3 hidden md:table-cell">Status</th>
                      <th className="text-right text-xs font-semibold text-dark-400 uppercase tracking-wider px-5 py-3">Total</th>
                    </tr>
                  </thead>
                  <tbody>
                    {RECENT_BOOKINGS.map((b, i) => (
                      <motion.tr
                        key={b.id}
                        initial={{ opacity: 0, x: -10 }}
                        animate={{ opacity: 1, x: 0 }}
                        transition={{ delay: i * 0.06 }}
                        className="border-b border-dark-700/60 last:border-0 hover:bg-white/2 transition-colors"
                      >
                        <td className="px-5 py-4">
                          <p className="font-medium text-white">{b.user}</p>
                          <p className="text-xs text-dark-500 font-mono">{b.id}</p>
                        </td>
                        <td className="px-5 py-4 hidden sm:table-cell text-dark-300">{b.car}</td>
                        <td className="px-5 py-4 hidden md:table-cell">
                          <span className={cn("badge border", STATUS_COLORS[b.status])}>
                            {b.status.charAt(0) + b.status.slice(1).toLowerCase()}
                          </span>
                        </td>
                        <td className="px-5 py-4 text-right font-semibold text-white">{formatCurrency(b.total)}</td>
                      </motion.tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </AnimatedSection>
          </div>

          {/* ── Fleet Status ── */}
          <div>
            <AnimatedSection>
              <div className="flex items-center justify-between mb-5">
                <h2 className="font-display text-lg font-bold text-white">Fleet Status</h2>
                <Link href="/admin/cars" className="text-sm text-brand-400 hover:text-brand-300 transition-colors">
                  Manage →
                </Link>
              </div>

              {/* Availability chart */}
              <div className="card p-5 mb-4">
                <div className="flex justify-between text-sm mb-3">
                  <span className="text-dark-400">Fleet Availability</span>
                  <span className="text-white font-semibold">{availableCount}/{MOCK_CARS.length} available</span>
                </div>
                <div className="h-3 bg-dark-700 rounded-full overflow-hidden mb-3">
                  <motion.div
                    initial={{ width: 0 }}
                    animate={{ width: `${(availableCount / MOCK_CARS.length) * 100}%` }}
                    transition={{ duration: 0.8, ease: "easeOut", delay: 0.3 }}
                    className="h-full bg-gradient-to-r from-brand-500 to-brand-400 rounded-full"
                  />
                </div>
                <div className="flex justify-between text-xs text-dark-500">
                  <span className="flex items-center gap-1.5"><span className="w-2 h-2 bg-brand-500 rounded-full" />{availableCount} Available</span>
                  <span className="flex items-center gap-1.5"><span className="w-2 h-2 bg-dark-600 rounded-full" />{unavailableCount} Booked</span>
                </div>
              </div>

              {/* Car list preview */}
              <div className="space-y-2">
                {MOCK_CARS.slice(0, 5).map((car) => (
                  <div key={car.id} className="flex items-center gap-3 p-3 rounded-xl bg-dark-800 border border-dark-700">
                    <div className="relative w-12 h-8 rounded-lg overflow-hidden shrink-0">
                      <Image src={car.images[0]} alt={car.make} fill className="object-cover" />
                    </div>
                    <div className="flex-1 min-w-0">
                      <p className="text-sm font-medium text-white truncate">{car.make} {car.model}</p>
                      <p className="text-xs text-dark-500">{formatCurrency(car.pricePerDay)}/day</p>
                    </div>
                    <div className={cn("w-2 h-2 rounded-full shrink-0", car.isAvailable ? "bg-green-400" : "bg-red-400")} />
                  </div>
                ))}
              </div>
            </AnimatedSection>
          </div>
        </div>
      </div>
    </AnimatedPage>
  );
}
