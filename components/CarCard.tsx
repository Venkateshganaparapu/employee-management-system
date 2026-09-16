"use client";

import { motion } from "framer-motion";
import Image from "next/image";
import Link from "next/link";
import { Star, Users, Fuel, Zap, Settings, MapPin, Check, X } from "lucide-react";
import { Car } from "@/lib/mock-data";
import { cn, formatCurrency, CATEGORY_COLORS, CATEGORY_LABELS } from "@/lib/utils";
import { fadeUpItem } from "./AnimatedPage";

interface CarCardProps {
  car: Car;
  index?: number;
}

export function CarCard({ car, index = 0 }: CarCardProps) {
  return (
    <motion.div
      variants={fadeUpItem}
      whileHover={{ y: -6, transition: { duration: 0.2 } }}
      className="card group cursor-pointer"
    >
      {/* Image */}
      <div className="relative h-52 overflow-hidden bg-dark-700">
        <Image
          src={car.images[0]}
          alt={`${car.make} ${car.model}`}
          fill
          className="object-cover transition-transform duration-500 group-hover:scale-105"
          sizes="(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 33vw"
        />

        {/* Overlays */}
        <div className="absolute inset-0 bg-gradient-to-t from-dark-900/80 via-transparent to-transparent" />

        {/* Badges */}
        <div className="absolute top-3 left-3 flex gap-2 flex-wrap">
          <span className={cn("badge border", CATEGORY_COLORS[car.category])}>
            {CATEGORY_LABELS[car.category]}
          </span>
          {car.isFeatured && (
            <span className="badge bg-brand-500/20 text-brand-300 border border-brand-500/30">
              ⭐ Featured
            </span>
          )}
        </div>

        {/* Availability */}
        <div className="absolute top-3 right-3">
          {car.isAvailable ? (
            <span className="badge bg-green-500/20 text-green-300 border border-green-500/30 gap-1">
              <Check className="w-3 h-3" /> Available
            </span>
          ) : (
            <span className="badge bg-red-500/20 text-red-300 border border-red-500/30 gap-1">
              <X className="w-3 h-3" /> Booked
            </span>
          )}
        </div>

        {/* Price on bottom of image */}
        <div className="absolute bottom-3 right-3">
          <span className="text-white font-bold text-lg bg-dark-900/80 backdrop-blur-sm px-3 py-1 rounded-lg">
            {formatCurrency(car.pricePerDay)}<span className="text-xs text-dark-300 font-normal">/day</span>
          </span>
        </div>
      </div>

      {/* Content */}
      <div className="p-5">
        {/* Title */}
        <div className="flex items-start justify-between mb-3">
          <div>
            <h3 className="font-display font-bold text-lg text-white group-hover:text-brand-400 transition-colors">
              {car.make} {car.model}
            </h3>
            <p className="text-dark-400 text-sm">{car.year} · {car.color}</p>
          </div>
          <div className="flex items-center gap-1 text-sm">
            <Star className="w-4 h-4 text-yellow-400 fill-yellow-400" />
            <span className="text-white font-semibold">{car.rating}</span>
            <span className="text-dark-400">({car.reviewCount})</span>
          </div>
        </div>

        {/* Specs */}
        <div className="grid grid-cols-3 gap-2 mb-4">
          <div className="flex items-center gap-1.5 text-xs text-dark-400">
            <Users className="w-3.5 h-3.5 text-dark-500" />
            {car.seats} seats
          </div>
          <div className="flex items-center gap-1.5 text-xs text-dark-400">
            <Settings className="w-3.5 h-3.5 text-dark-500" />
            {car.transmission}
          </div>
          <div className="flex items-center gap-1.5 text-xs text-dark-400">
            {car.fuelType === "Electric" ? (
              <Zap className="w-3.5 h-3.5 text-teal-400" />
            ) : (
              <Fuel className="w-3.5 h-3.5 text-dark-500" />
            )}
            {car.fuelType}
          </div>
        </div>

        {/* Location */}
        <div className="flex items-center gap-1.5 text-xs text-dark-400 mb-4">
          <MapPin className="w-3.5 h-3.5 text-dark-500" />
          {car.location}
        </div>

        {/* CTA */}
        <Link
          href={`/cars/${car.id}`}
          className={cn(
            "block w-full text-center py-2.5 rounded-xl text-sm font-semibold transition-all duration-200",
            car.isAvailable
              ? "bg-brand-500 hover:bg-brand-600 text-white shadow-lg shadow-brand-500/20"
              : "bg-dark-700 text-dark-400 cursor-not-allowed pointer-events-none"
          )}
        >
          {car.isAvailable ? "View & Book" : "Currently Unavailable"}
        </Link>
      </div>
    </motion.div>
  );
}
