"use client"

import { useEffect, useState } from "react"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Badge } from "@/components/ui/badge"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, LineChart, Line } from "recharts"
import { DollarSign, TrendingUp, Package } from "lucide-react"

interface Sale {
  id: number
  quantity_sold: number
  unit_price_ars: number
  unit_price_usd: number
  total_amount_ars: number
  total_amount_usd: number
  sale_date: string
  buyer_name: string
  batch: {
    id: number
    animal_type: {
      name: string
    }
  }
}

export default function VentasPage() {
  const [sales, setSales] = useState<Sale[]>([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    fetchSales()
  }, [])

  const fetchSales = async () => {
    try {
      const token = localStorage.getItem("token")
      const response = await fetch("http://127.0.0.1:8000/api/sales", {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      })

      if (response.ok) {
        const data = await response.json()
        setSales(data)
      }
    } catch (error) {
      console.error("Error fetching sales:", error)
    } finally {
      setLoading(false)
    }
  }

  // Mock data for demonstration
  const mockSales = [
    {
      id: 1,
      quantity_sold: 10,
      unit_price_ars: 45000,
      unit_price_usd: 180,
      total_amount_ars: 450000,
      total_amount_usd: 1800,
      sale_date: "2024-01-15",
      buyer_name: "Frigorífico San Juan",
      batch: {
        id: 1,
        animal_type: { name: "Bovino" },
      },
    },
    {
      id: 2,
      quantity_sold: 15,
      unit_price_ars: 42000,
      unit_price_usd: 168,
      total_amount_ars: 630000,
      total_amount_usd: 2520,
      sale_date: "2024-01-20",
      buyer_name: "Carnicería Central",
      batch: {
        id: 2,
        animal_type: { name: "Porcino" },
      },
    },
    {
      id: 3,
      quantity_sold: 8,
      unit_price_ars: 48000,
      unit_price_usd: 192,
      total_amount_ars: 384000,
      total_amount_usd: 1536,
      sale_date: "2024-01-25",
      buyer_name: "Exportadora del Sur",
      batch: {
        id: 3,
        animal_type: { name: "Ovino" },
      },
    },
  ]

  const displaySales = sales.length > 0 ? sales : mockSales

  // Calculate statistics
  const totalSalesARS = displaySales.reduce((sum, sale) => sum + sale.total_amount_ars, 0)
  const totalSalesUSD = displaySales.reduce((sum, sale) => sum + sale.total_amount_usd, 0)
  const totalAnimals = displaySales.reduce((sum, sale) => sum + sale.quantity_sold, 0)
  const averagePriceARS = totalSalesARS / totalAnimals || 0
  const averagePriceUSD = totalSalesUSD / totalAnimals || 0

  // Chart data
  const monthlyData = [
    { month: "Ene", ventas: 450000, cantidad: 10 },
    { month: "Feb", ventas: 630000, cantidad: 15 },
    { month: "Mar", ventas: 384000, cantidad: 8 },
    { month: "Abr", ventas: 720000, cantidad: 18 },
    { month: "May", ventas: 560000, cantidad: 12 },
    { month: "Jun", ventas: 890000, cantidad: 22 },
  ]

  if (loading) {
    return (
      <div className="p-6">
        <div className="animate-pulse space-y-6">
          <div className="h-8 bg-gray-200 rounded w-1/4"></div>
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            {[...Array(4)].map((_, i) => (
              <div key={i} className="h-32 bg-gray-200 rounded"></div>
            ))}
          </div>
        </div>
      </div>
    )
  }

  return (
    <div className="p-6 space-y-6">
      <div>
        <h1 className="text-3xl font-bold text-gray-900">Historial de Ventas</h1>
        <p className="text-gray-600">Análisis y seguimiento de tus ventas</p>
      </div>

      {/* Stats Cards */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Total Ventas ARS</CardTitle>
            <DollarSign className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">${totalSalesARS.toLocaleString()}</div>
            <p className="text-xs text-muted-foreground">{displaySales.length} ventas realizadas</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Total Ventas USD</CardTitle>
            <DollarSign className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">${totalSalesUSD.toLocaleString()}</div>
            <p className="text-xs text-muted-foreground">Equivalente en dólares</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Animales Vendidos</CardTitle>
            <Package className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{totalAnimals}</div>
            <p className="text-xs text-muted-foreground">Total de cabezas</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Precio Promedio</CardTitle>
            <TrendingUp className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">${Math.round(averagePriceARS).toLocaleString()}</div>
            <p className="text-xs text-muted-foreground">ARS por animal</p>
          </CardContent>
        </Card>
      </div>

      {/* Charts */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <Card>
          <CardHeader>
            <CardTitle>Ventas Mensuales</CardTitle>
            <CardDescription>Evolución de ventas por mes</CardDescription>
          </CardHeader>
          <CardContent>
            <ResponsiveContainer width="100%" height={300}>
              <BarChart data={monthlyData}>
                <CartesianGrid strokeDasharray="3 3" />
                <XAxis dataKey="month" />
                <YAxis />
                <Tooltip
                  formatter={(value, name) => [
                    name === "ventas" ? `$${value.toLocaleString()}` : value,
                    name === "ventas" ? "Ventas ARS" : "Cantidad",
                  ]}
                />
                <Bar dataKey="ventas" fill="#16a34a" />
              </BarChart>
            </ResponsiveContainer>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Tendencia de Cantidad</CardTitle>
            <CardDescription>Animales vendidos por mes</CardDescription>
          </CardHeader>
          <CardContent>
            <ResponsiveContainer width="100%" height={300}>
              <LineChart data={monthlyData}>
                <CartesianGrid strokeDasharray="3 3" />
                <XAxis dataKey="month" />
                <YAxis />
                <Tooltip />
                <Line type="monotone" dataKey="cantidad" stroke="#2563eb" strokeWidth={2} />
              </LineChart>
            </ResponsiveContainer>
          </CardContent>
        </Card>
      </div>

      {/* Sales Table */}
      <Card>
        <CardHeader>
          <CardTitle>Detalle de Ventas</CardTitle>
          <CardDescription>Historial completo de todas las ventas realizadas</CardDescription>
        </CardHeader>
        <CardContent>
          {displaySales.length === 0 ? (
            <div className="text-center py-8">
              <p className="text-gray-500">No hay ventas registradas</p>
            </div>
          ) : (
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Fecha</TableHead>
                  <TableHead>Comprador</TableHead>
                  <TableHead>Tipo Animal</TableHead>
                  <TableHead>Cantidad</TableHead>
                  <TableHead>Precio Unit. ARS</TableHead>
                  <TableHead>Precio Unit. USD</TableHead>
                  <TableHead>Total ARS</TableHead>
                  <TableHead>Total USD</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {displaySales.map((sale) => (
                  <TableRow key={sale.id}>
                    <TableCell>{new Date(sale.sale_date).toLocaleDateString()}</TableCell>
                    <TableCell className="font-medium">{sale.buyer_name}</TableCell>
                    <TableCell>
                      <Badge variant="secondary">{sale.batch.animal_type.name}</Badge>
                    </TableCell>
                    <TableCell>{sale.quantity_sold}</TableCell>
                    <TableCell>${sale.unit_price_ars.toLocaleString()}</TableCell>
                    <TableCell>${sale.unit_price_usd.toLocaleString()}</TableCell>
                    <TableCell className="font-medium">${sale.total_amount_ars.toLocaleString()}</TableCell>
                    <TableCell className="font-medium text-green-600">
                      ${sale.total_amount_usd.toLocaleString()}
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          )}
        </CardContent>
      </Card>
    </div>
  )
}
