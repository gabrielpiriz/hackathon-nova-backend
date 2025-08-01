"use client"

import { useEffect, useState } from "react"
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Badge } from "@/components/ui/badge"
import { Plus, Eye, Edit, Trash2 } from "lucide-react"
import Link from "next/link"
import { useToast } from "@/hooks/use-toast"

interface Batch {
  id: number
  quantity: number
  age_months: number
  average_weight_kg: number
  suggested_price_ars: number
  suggested_price_usd: number
  status: string
  animal_type: {
    id: number
    name: string
    description: string
  }
}

export default function LotesPage() {
  const [batches, setBatches] = useState<Batch[]>([])
  const [loading, setLoading] = useState(true)
  const { toast } = useToast()

  useEffect(() => {
    fetchBatches()
  }, [])

  const fetchBatches = async () => {
    try {
      const token = localStorage.getItem("token")
      const response = await fetch("http://127.0.0.1:8000/api/batches", {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      })

      if (response.ok) {
        const data = await response.json()
        setBatches(data)
      }
    } catch (error) {
      console.error("Error fetching batches:", error)
      toast({
        title: "Error",
        description: "No se pudieron cargar los lotes",
        variant: "destructive",
      })
    } finally {
      setLoading(false)
    }
  }

  const deleteBatch = async (id: number) => {
    if (!confirm("¿Estás seguro de que quieres eliminar este lote?")) {
      return
    }

    try {
      const token = localStorage.getItem("token")
      const response = await fetch(`http://127.0.0.1:8000/api/batches/${id}`, {
        method: "DELETE",
        headers: {
          Authorization: `Bearer ${token}`,
        },
      })

      if (response.ok) {
        setBatches(batches.filter((batch) => batch.id !== id))
        toast({
          title: "Éxito",
          description: "Lote eliminado correctamente",
        })
      }
    } catch (error) {
      console.error("Error deleting batch:", error)
      toast({
        title: "Error",
        description: "No se pudo eliminar el lote",
        variant: "destructive",
      })
    }
  }

  const getStatusColor = (status: string) => {
    switch (status.toLowerCase()) {
      case "disponible":
        return "bg-green-100 text-green-800"
      case "vendido":
        return "bg-gray-100 text-gray-800"
      case "reservado":
        return "bg-yellow-100 text-yellow-800"
      default:
        return "bg-blue-100 text-blue-800"
    }
  }

  if (loading) {
    return (
      <div className="p-6">
        <div className="animate-pulse space-y-6">
          <div className="h-8 bg-gray-200 rounded w-1/4"></div>
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {[...Array(6)].map((_, i) => (
              <div key={i} className="h-64 bg-gray-200 rounded"></div>
            ))}
          </div>
        </div>
      </div>
    )
  }

  return (
    <div className="p-6 space-y-6">
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-3xl font-bold text-gray-900">Gestión de Lotes</h1>
          <p className="text-gray-600">Administra tus lotes de ganado</p>
        </div>
        <Link href="/lotes/nuevo">
          <Button>
            <Plus className="mr-2 h-4 w-4" />
            Nuevo Lote
          </Button>
        </Link>
      </div>

      {batches.length === 0 ? (
        <Card>
          <CardContent className="flex flex-col items-center justify-center py-12">
            <div className="text-center">
              <h3 className="text-lg font-medium text-gray-900 mb-2">No tienes lotes registrados</h3>
              <p className="text-gray-600 mb-4">Comienza creando tu primer lote de ganado</p>
              <Link href="/lotes/nuevo">
                <Button>
                  <Plus className="mr-2 h-4 w-4" />
                  Crear Primer Lote
                </Button>
              </Link>
            </div>
          </CardContent>
        </Card>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {batches.map((batch) => (
            <Card key={batch.id} className="hover:shadow-lg transition-shadow">
              <CardHeader>
                <div className="flex justify-between items-start">
                  <div>
                    <CardTitle className="text-lg">{batch.animal_type.name}</CardTitle>
                    <CardDescription>Lote #{batch.id}</CardDescription>
                  </div>
                  <Badge className={getStatusColor(batch.status)}>{batch.status}</Badge>
                </div>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="grid grid-cols-2 gap-4 text-sm">
                  <div>
                    <span className="text-gray-600">Cantidad:</span>
                    <p className="font-medium">{batch.quantity} animales</p>
                  </div>
                  <div>
                    <span className="text-gray-600">Edad:</span>
                    <p className="font-medium">{batch.age_months} meses</p>
                  </div>
                  <div>
                    <span className="text-gray-600">Peso promedio:</span>
                    <p className="font-medium">{batch.average_weight_kg} kg</p>
                  </div>
                  <div>
                    <span className="text-gray-600">Precio ARS:</span>
                    <p className="font-medium">${batch.suggested_price_ars.toLocaleString()}</p>
                  </div>
                </div>

                <div className="text-sm">
                  <span className="text-gray-600">Precio USD:</span>
                  <p className="font-medium text-lg text-green-600">${batch.suggested_price_usd.toLocaleString()}</p>
                </div>

                <div className="flex justify-between pt-4 border-t">
                  <Link href={`/lotes/${batch.id}`}>
                    <Button variant="outline" size="sm">
                      <Eye className="mr-2 h-4 w-4" />
                      Ver
                    </Button>
                  </Link>
                  <div className="space-x-2">
                    <Link href={`/lotes/${batch.id}/editar`}>
                      <Button variant="outline" size="sm">
                        <Edit className="h-4 w-4" />
                      </Button>
                    </Link>
                    <Button
                      variant="outline"
                      size="sm"
                      onClick={() => deleteBatch(batch.id)}
                      className="text-red-600 hover:text-red-700"
                    >
                      <Trash2 className="h-4 w-4" />
                    </Button>
                  </div>
                </div>
              </CardContent>
            </Card>
          ))}
        </div>
      )}
    </div>
  )
}
